<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ClassReflection;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PHPStan\ScopeFetcher;
use Rector\PHPUnit\Enum\AssertMethod;
use Rector\PHPUnit\Enum\PHPUnitClassName;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\FuncCall\AssertFuncCallToPHPUnitAssertRector\AssertFuncCallToPHPUnitAssertRectorTest
 */
final class AssertFuncCallToPHPUnitAssertRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    public function __construct(ValueResolver $valueResolver, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->valueResolver = $valueResolver;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turns assert() calls in tests to PHPUnit assert method alternative', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    public function test()
    {
        $value = 1000;
        assert($value === 1000, 'message');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    public function test()
    {
        $value = 1000;

        $this->assertSame(1000, $value, "message")
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return class-string[]
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
        if (!$this->testsNodeAnalyzer->isInTestClass($node) && !$this->isBehatContext($node)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if ($classMethod->stmts === null) {
                continue;
            }
            $useStaticAssert = $classMethod->isStatic() ?: $this->isBehatContext($node);
            $this->traverseNodesWithCallable($classMethod->stmts, function (Node $node) use (&$useStaticAssert, &$hasChanged) {
                if ($node instanceof Closure && $node->static) {
                    $useStaticAssert = \true;
                    return null;
                }
                if (!$node instanceof FuncCall) {
                    return null;
                }
                if ($node->isFirstClassCallable()) {
                    return null;
                }
                if (!$this->isName($node, 'assert')) {
                    return null;
                }
                $comparedExpr = $node->getArgs()[0]->value;
                if ($comparedExpr instanceof Equal) {
                    $methodName = AssertMethod::ASSERT_EQUALS;
                    $exprs = [$comparedExpr->right, $comparedExpr->left];
                } elseif ($comparedExpr instanceof Identical) {
                    $methodName = AssertMethod::ASSERT_SAME;
                    $exprs = [$comparedExpr->right, $comparedExpr->left];
                } elseif ($comparedExpr instanceof NotIdentical) {
                    if ($this->valueResolver->isNull($comparedExpr->right)) {
                        $methodName = 'assertNotNull';
                        $exprs = [$comparedExpr->left];
                    } else {
                        return null;
                    }
                } elseif ($comparedExpr instanceof Bool_) {
                    $methodName = 'assertTrue';
                    $exprs = [$comparedExpr];
                } elseif ($comparedExpr instanceof FuncCall) {
                    if ($this->isName($comparedExpr, 'method_exists')) {
                        $methodName = 'assertTrue';
                        $exprs = [$comparedExpr];
                    } else {
                        return null;
                    }
                } elseif ($comparedExpr instanceof Instanceof_) {
                    // outside TestCase
                    $methodName = 'assertInstanceOf';
                    $exprs = [];
                    if ($comparedExpr->class instanceof FullyQualified) {
                        $classConstFetch = new ClassConstFetch($comparedExpr->class, 'class');
                        $exprs[] = $classConstFetch;
                    } else {
                        return null;
                    }
                    $exprs[] = $comparedExpr->expr;
                } else {
                    return null;
                }
                // is there a comment message
                if (isset($node->getArgs()[1])) {
                    $exprs[] = $node->getArgs()[1]->value;
                }
                $hasChanged = \true;
                return $this->createAssertCall($methodName, $exprs, $useStaticAssert);
            });
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    private function isBehatContext(Class_ $class): bool
    {
        $scope = ScopeFetcher::fetch($class);
        if (!$scope->getClassReflection() instanceof ClassReflection) {
            return \false;
        }
        $className = $scope->getClassReflection()->getName();
        // special case with static call
        return substr_compare($className, 'Context', -strlen('Context')) === 0;
    }
    /**
     * @param Expr[] $exprs
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall
     */
    private function createAssertCall(string $methodName, array $exprs, bool $useStaticAssert)
    {
        $args = [];
        foreach ($exprs as $expr) {
            $args[] = new Arg($expr);
        }
        if ($useStaticAssert) {
            $assertFullyQualified = new FullyQualified(PHPUnitClassName::ASSERT);
            return new StaticCall($assertFullyQualified, $methodName, $args);
        }
        return new MethodCall(new Variable('this'), $methodName, $args);
    }
}
