<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\Exception\NotImplementedYetException;
use Rector\Exception\ShouldNotHappenException;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\Rector\ClassMethod\CreateMockToAnonymousClassRector\CreateMockToAnonymousClassRectorTest
 */
final class CreateMockToAnonymousClassRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change $this->createMock() with methods to direct anonymous class', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $someMockObject = $this->createMock(SomeClass::class);

        $someMockObject->method('someMethod')
            ->willReturn(100);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $someMockObject = new class extends SomeClass {
            public function someMethod()
            {
                return 100;
            }
        };
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
    public function refactor(Node $node) : ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        if ($node->stmts === []) {
            return null;
        }
        $anonymousClassPosition = null;
        $anonymousClass = null;
        $mockExpr = null;
        $hasDynamicReturnExprs = \false;
        $anonymousClassMethods = [];
        $createMockMethodCallAssign = null;
        foreach ((array) $node->stmts as $key => $classMethodStmt) {
            if ($mockExpr instanceof Expr) {
                // possible call on mock expr
                if ($classMethodStmt instanceof Expression && $classMethodStmt->expr instanceof MethodCall) {
                    $methodCall = $classMethodStmt->expr;
                    $rootMethodCall = $methodCall;
                    while ($rootMethodCall->var instanceof MethodCall) {
                        $rootMethodCall = $rootMethodCall->var;
                    }
                    if (!$this->nodeComparator->areNodesEqual($rootMethodCall->var, $mockExpr)) {
                        continue;
                    }
                    if (!$this->isName($rootMethodCall->name, 'method')) {
                        continue;
                    }
                    if ($methodCall->isFirstClassCallable()) {
                        continue;
                    }
                    // has dynamic return?
                    if ($hasDynamicReturnExprs === \false) {
                        $returnedExpr = $methodCall->getArgs()[0]->value;
                        $hasDynamicReturnExprs = !$returnedExpr instanceof Scalar && !$returnedExpr instanceof Array_;
                    }
                    $anonymousClassMethods[$key] = $this->createMockedClassMethod($rootMethodCall, $methodCall);
                }
                continue;
            }
            $createMockMethodCallAssign = $this->matchCreateMockAssign($classMethodStmt);
            if (!$createMockMethodCallAssign instanceof Assign) {
                continue;
            }
            // change to anonymous class
            /** @var MethodCall $methodCall */
            $methodCall = $createMockMethodCallAssign->expr;
            if ($methodCall->isFirstClassCallable()) {
                continue;
            }
            $firstArg = $methodCall->getArgs()[0];
            $mockExpr = $createMockMethodCallAssign->var;
            $anonymousClass = $this->createAnonymousClass($firstArg);
            $anonymousClassPosition = $key;
        }
        if ($anonymousClassPosition === null) {
            return null;
        }
        if (!$anonymousClass instanceof Class_) {
            return null;
        }
        if ($hasDynamicReturnExprs) {
            return null;
        }
        foreach ($anonymousClassMethods as $keyToRemove => $anonymousClassMethod) {
            unset($node->stmts[$keyToRemove]);
            $anonymousClass->stmts[] = $anonymousClassMethod;
        }
        if (!$createMockMethodCallAssign instanceof Assign) {
            throw new ShouldNotHappenException();
        }
        $new = new New_($anonymousClass);
        $newAnonymousClassAssign = new Assign($createMockMethodCallAssign->var, $new);
        $node->stmts[$anonymousClassPosition] = new Expression($newAnonymousClassAssign);
        return $node;
    }
    private function createAnonymousClass(Arg $firstArg) : Class_
    {
        if ($firstArg->value instanceof ClassConstFetch) {
            $className = $firstArg->value->class;
        } else {
            throw new NotImplementedYetException();
        }
        // must respect PHPStan anonymous internal naming \Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver::ANONYMOUS_CLASS_START_REGEX
        return new Class_('AnonymousClass1234', ['extends' => $className], ['startLine' => $firstArg->getStartLine(), 'endLine' => $firstArg->getEndLine()]);
    }
    private function matchCreateMockAssign(Stmt $stmt) : ?Assign
    {
        if (!$stmt instanceof Expression) {
            return null;
        }
        if (!$stmt->expr instanceof Assign) {
            return null;
        }
        // assign method call to variable
        $assign = $stmt->expr;
        if (!$assign->expr instanceof MethodCall) {
            return null;
        }
        if (!$this->isName($assign->expr->name, 'createMock')) {
            return null;
        }
        return $assign;
    }
    private function createMockedClassMethod(MethodCall $rootMethodCall, MethodCall $methodCall) : ClassMethod
    {
        $rootMethodCallFirstArg = $rootMethodCall->getArgs()[0];
        $methodNameExpr = $rootMethodCallFirstArg->value;
        if ($methodNameExpr instanceof String_) {
            $methodName = $methodNameExpr->value;
        } else {
            throw new NotImplementedYetException();
        }
        $returnedExpr = $methodCall->getArgs()[0]->value;
        return new ClassMethod($methodName, ['flags' => Class_::MODIFIER_PUBLIC, 'stmts' => [new Return_($returnedExpr)]]);
    }
}
