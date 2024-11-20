<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeFinder;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PHPUnit\CodeQuality\NodeAnalyser\DoctrineEntityDocumentAnalyser;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\ClassMethod\EntityDocumentCreateMockToDirectNewRector\EntityDocumentCreateMockToDirectNewRectorTest
 */
final class EntityDocumentCreateMockToDirectNewRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @readonly
     */
    private DoctrineEntityDocumentAnalyser $doctrineEntityDocumentAnalyser;
    public function __construct(ValueResolver $valueResolver, ReflectionProvider $reflectionProvider, DoctrineEntityDocumentAnalyser $doctrineEntityDocumentAnalyser)
    {
        $this->valueResolver = $valueResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->doctrineEntityDocumentAnalyser = $doctrineEntityDocumentAnalyser;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Move from entity mock, to direct use of class instance and setters', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeFileTest extends TestCase
{
    public function test()
    {
        $tableMock = $this->createMock(Table::class);

        $tableMock->expects(self::once())
            ->method('isLocked')
            ->willReturn(true);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeFileTest extends TestCase
{
    public function test()
    {
        $table = new Table();
        $table->setLocked(true);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?ClassMethod
    {
        if ($node->stmts === null) {
            return null;
        }
        $mockedVariablesToTypes = $this->collectMockedVariableToTypeAndRefactorAssign($node);
        if ($mockedVariablesToTypes === []) {
            return null;
        }
        foreach ($mockedVariablesToTypes as $mockedVariableName => $mockedClass) {
            foreach ($node->stmts as $key => $stmt) {
                if (!$stmt instanceof Expression) {
                    continue;
                }
                if (!$stmt->expr instanceof MethodCall) {
                    continue;
                }
                $methodCall = $stmt->expr;
                $onVariableMethodCall = $this->findMethodCallOnVariableNamed($methodCall, $mockedVariableName);
                if (!$onVariableMethodCall instanceof Node) {
                    continue;
                }
                // 2. find $mock->method("name")
                $methodName = $this->resolveMethodCallFirstArgValue($methodCall, 'method');
                if (!\is_string($methodName)) {
                    throw new ShouldNotHappenException('Unable to resolve method name');
                }
                // is set method mocked? Just remove it
                if (\strncmp($methodName, 'set', \strlen('set')) === 0) {
                    unset($node->stmts[$key]);
                }
                $methodName = $this->resolveMethodName($methodName, $mockedClass);
                $willReturnExpr = $this->resolveMethodCallFirstArgValue($methodCall, 'willReturn');
                if ($methodName && $willReturnExpr instanceof Expr) {
                    $stmt->expr = new MethodCall(new Variable($mockedVariableName), new Identifier($methodName), [new Arg($willReturnExpr)]);
                }
            }
        }
        // 3. replace value without "mock" in name
        $mockedVariableNames = \array_keys($mockedVariablesToTypes);
        $this->traverseNodesWithCallable($node, function (Node $node) use($mockedVariableNames) : ?Variable {
            if (!$node instanceof Variable) {
                return null;
            }
            if (!\is_string($node->name)) {
                return null;
            }
            if (!\in_array($node->name, $mockedVariableNames)) {
                return null;
            }
            return new Variable(\str_replace('Mock', '', $node->name));
        });
        return $node;
    }
    /**
     * @return array<string, string>
     */
    private function collectMockedVariableToTypeAndRefactorAssign(ClassMethod $classMethod) : array
    {
        if ($classMethod->stmts === null) {
            return [];
        }
        $mockedVariablesToTypes = [];
        foreach ($classMethod->stmts as $stmt) {
            // find assign mock
            if (!$stmt instanceof Expression) {
                continue;
            }
            $stmtExpr = $stmt->expr;
            if (!$stmtExpr instanceof Assign) {
                continue;
            }
            /** @var Assign $assign */
            $assign = $stmtExpr;
            if (!$assign->expr instanceof MethodCall) {
                continue;
            }
            $methodCall = $assign->expr;
            if (!$this->isName($methodCall->name, 'createMock')) {
                continue;
            }
            $firstArg = $methodCall->getArgs()[0];
            $mockedClass = $this->valueResolver->getValue($firstArg->value);
            if (!\is_string($mockedClass)) {
                continue;
            }
            if (!$this->reflectionProvider->hasClass($mockedClass)) {
                continue;
            }
            $mockClassReflection = $this->reflectionProvider->getClass($mockedClass);
            if ($mockClassReflection->isAbstract()) {
                continue;
            }
            if (!$this->doctrineEntityDocumentAnalyser->isEntityClass($mockClassReflection)) {
                continue;
            }
            // ready to replace :)
            $assign->expr = new New_(new FullyQualified($mockedClass));
            $mockedVariableName = $this->getName($assign->var);
            $mockedVariablesToTypes[$mockedVariableName] = $mockedClass;
        }
        return $mockedVariablesToTypes;
    }
    /**
     * @return string|\PhpParser\Node\Expr|null
     */
    private function resolveMethodCallFirstArgValue(MethodCall $methodCall, string $methodName)
    {
        $nodeFinder = new NodeFinder();
        $methodNameMethodCall = $nodeFinder->findFirst($methodCall, function (Node $node) use($methodName) : bool {
            if (!$node instanceof MethodCall) {
                return \false;
            }
            return $this->isName($node->name, $methodName);
        });
        if (!$methodNameMethodCall instanceof MethodCall) {
            return null;
        }
        $methodNameArg = $methodNameMethodCall->getArgs()[0];
        if ($methodNameArg->value instanceof String_) {
            return $methodNameArg->value->value;
        }
        return $methodNameArg->value;
    }
    private function resolveMethodName(string $methodName, string $mockedClass) : string
    {
        // guess the setter name
        if (\strncmp($methodName, 'get', \strlen('get')) === 0) {
            return 'set' . \ucfirst(\substr($methodName, 3));
        }
        if (\strncmp($methodName, 'is', \strlen('is')) === 0) {
            $mockedClassReflection = $this->reflectionProvider->getClass($mockedClass);
            $isSetterMethodNames = ['set' . \ucfirst($methodName), 'set' . \substr($methodName, 2)];
            foreach ($isSetterMethodNames as $isSetterMethodName) {
                if ($mockedClassReflection->hasMethod($isSetterMethodName)) {
                    return $isSetterMethodName;
                }
            }
        }
        return $methodName;
    }
    private function findMethodCallOnVariableNamed(MethodCall $methodCall, string $desiredVariableName) : ?MethodCall
    {
        $nodeFinder = new NodeFinder();
        $foundMethodCall = $nodeFinder->findFirst($methodCall, function (Node $node) use($desiredVariableName) : bool {
            if (!$node instanceof MethodCall) {
                return \false;
            }
            if (!$node->var instanceof Variable) {
                return \false;
            }
            return $this->isName($node->var, $desiredVariableName);
        });
        if (!$foundMethodCall instanceof MethodCall) {
            return null;
        }
        return $foundMethodCall;
    }
}
