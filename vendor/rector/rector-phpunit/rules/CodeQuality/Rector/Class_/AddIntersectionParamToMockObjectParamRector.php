<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareIntersectionTypeNode;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPUnit\Enum\PHPUnitClassName;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\Class_\AddIntersectionParamToMockObjectParamRector\AddIntersectionParamToMockObjectParamRectorTest
 */
final class AddIntersectionParamToMockObjectParamRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private PhpDocTypeChanger $phpDocTypeChanger;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, BetterNodeFinder $betterNodeFinder, PhpDocInfoFactory $phpDocInfoFactory, PhpDocTypeChanger $phpDocTypeChanger)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
    }
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        return new ComposerPackageConstraint('phpunit/phpunit', '>=11.0');
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add a MockObject intersection @param docblock with the mocked class, based on the mock passed in the private method call', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test(): void
    {
        $someService = $this->createMock(SomeService::class);
        $this->prepareServiceMock($someService);
    }

    private function prepareServiceMock(MockObject $someService): void
    {
        $someService->expects($this->once())
            ->method('getId');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test(): void
    {
        $someService = $this->createMock(SomeService::class);
        $this->prepareServiceMock($someService);
    }

    /**
     * @param \SomeService&\PHPUnit\Framework\MockObject\MockObject $someService
     */
    private function prepareServiceMock(MockObject $someService): void
    {
        $someService->expects($this->once())
            ->method('getId');
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Class_
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $mockObjectParamClassMethods = $this->collectMockObjectParamClassMethods($node);
        if ($mockObjectParamClassMethods === []) {
            return null;
        }
        $mockedClassesByMethodName = $this->resolveMockedClassesFromCallSites($node, array_keys($mockObjectParamClassMethods));
        $hasChanged = \false;
        foreach ($mockObjectParamClassMethods as $methodName => $classMethod) {
            $mockedClassesByPosition = $mockedClassesByMethodName[$methodName] ?? [];
            foreach ($classMethod->params as $position => $param) {
                if (!$this->isBareMockObjectParam($param)) {
                    continue;
                }
                $mockedClass = $this->resolveSingleMockedClass($mockedClassesByPosition[$position] ?? []);
                if ($mockedClass === null) {
                    continue;
                }
                $paramName = $this->getName($param->var);
                if ($paramName === null) {
                    continue;
                }
                $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
                // do not contradict an existing docblock type
                if ($phpDocInfo->getParamTagValueByName($paramName) instanceof ParamTagValueNode) {
                    continue;
                }
                $intersectionTypeNode = new BracketsAwareIntersectionTypeNode([new IdentifierTypeNode('\\' . $mockedClass), new IdentifierTypeNode('\\' . PHPUnitClassName::MOCK_OBJECT)]);
                $this->phpDocTypeChanger->changeParamTypeNode($classMethod, $phpDocInfo, $param, $paramName, $intersectionTypeNode);
                $hasChanged = \true;
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @return array<string, ClassMethod>
     */
    private function collectMockObjectParamClassMethods(Class_ $class): array
    {
        $mockObjectParamClassMethods = [];
        foreach ($class->getMethods() as $classMethod) {
            // only private methods, as those can only be called from the very same class
            if (!$classMethod->isPrivate()) {
                continue;
            }
            if ($classMethod->stmts === null) {
                continue;
            }
            foreach ($classMethod->params as $param) {
                if (!$this->isBareMockObjectParam($param)) {
                    continue;
                }
                $mockObjectParamClassMethods[$this->getName($classMethod)] = $classMethod;
                break;
            }
        }
        return $mockObjectParamClassMethods;
    }
    /**
     * @param string[] $methodNames
     * @return array<string, array<int, array<string|null>>>
     */
    private function resolveMockedClassesFromCallSites(Class_ $class, array $methodNames): array
    {
        $mockedClassesByMethodName = [];
        foreach ($class->getMethods() as $classMethod) {
            if ($classMethod->stmts === null) {
                continue;
            }
            $mockedClassesByVariableName = $this->resolveMockedClassesByVariableName($classMethod);
            /** @var MethodCall[] $methodCalls */
            $methodCalls = $this->betterNodeFinder->findInstancesOfScoped((array) $classMethod->stmts, MethodCall::class);
            foreach ($methodCalls as $methodCall) {
                if (!$this->isName($methodCall->var, 'this')) {
                    continue;
                }
                $calledMethodName = $this->getName($methodCall->name);
                if (!in_array($calledMethodName, $methodNames, \true)) {
                    continue;
                }
                foreach ($methodCall->getArgs() as $position => $arg) {
                    $mockedClassesByMethodName[$calledMethodName][$position][] = $this->resolveArgMockedClass($arg, $mockedClassesByVariableName);
                }
            }
        }
        return $mockedClassesByMethodName;
    }
    /**
     * @param array<string, string|null> $mockedClassesByVariableName
     */
    private function resolveArgMockedClass(Arg $arg, array $mockedClassesByVariableName): ?string
    {
        // named args make the position unreliable
        if ($arg->name instanceof Identifier) {
            return null;
        }
        if ($arg->unpack) {
            return null;
        }
        if ($arg->value instanceof Variable) {
            $variableName = $this->getName($arg->value);
            if ($variableName !== null) {
                return $mockedClassesByVariableName[$variableName] ?? null;
            }
            return null;
        }
        return $this->resolveCreateMockClass($arg->value);
    }
    /**
     * Variables assigned exactly once from a createMock() call
     *
     * @return array<string, string|null>
     */
    private function resolveMockedClassesByVariableName(ClassMethod $classMethod): array
    {
        $mockedClassesByVariableName = [];
        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstancesOfScoped((array) $classMethod->stmts, Assign::class);
        foreach ($assigns as $assign) {
            if (!$assign->var instanceof Variable) {
                continue;
            }
            $variableName = $this->getName($assign->var);
            if ($variableName === null) {
                continue;
            }
            // re-assigned variable, the type is not reliable
            if (array_key_exists($variableName, $mockedClassesByVariableName)) {
                $mockedClassesByVariableName[$variableName] = null;
                continue;
            }
            $mockedClassesByVariableName[$variableName] = $this->resolveCreateMockClass($assign->expr);
        }
        return $mockedClassesByVariableName;
    }
    private function resolveCreateMockClass(Expr $expr): ?string
    {
        // both $this->createMock() and self::createMock()
        if (!$expr instanceof MethodCall && !$expr instanceof StaticCall) {
            return null;
        }
        if (!$this->isName($expr->name, 'createMock')) {
            return null;
        }
        $firstArg = $expr->getArgs()[0] ?? null;
        if (!$firstArg instanceof Arg) {
            return null;
        }
        if (!$firstArg->value instanceof ClassConstFetch) {
            return null;
        }
        $className = $this->getName($firstArg->value->class);
        if (!is_string($className)) {
            return null;
        }
        return $className;
    }
    /**
     * @param array<string|null> $mockedClasses
     */
    private function resolveSingleMockedClass(array $mockedClasses): ?string
    {
        if ($mockedClasses === []) {
            return null;
        }
        // every call site must pass a mock of the very same class
        $uniqueMockedClasses = array_unique($mockedClasses, \SORT_REGULAR);
        if (count($uniqueMockedClasses) !== 1) {
            return null;
        }
        return array_pop($uniqueMockedClasses);
    }
    private function isBareMockObjectParam(Param $param): bool
    {
        if ($param->variadic) {
            return \false;
        }
        if (!$param->var instanceof Variable) {
            return \false;
        }
        if (!$param->type instanceof Name) {
            return \false;
        }
        return $this->isName($param->type, PHPUnitClassName::MOCK_OBJECT);
    }
}
