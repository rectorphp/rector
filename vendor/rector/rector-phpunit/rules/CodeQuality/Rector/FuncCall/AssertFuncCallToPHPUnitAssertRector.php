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
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Reflection\ClassReflection;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PHPStan\ScopeFetcher;
use Rector\PHPUnit\Enum\AssertMethod;
use Rector\PHPUnit\Enum\PHPUnitClassName;
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
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns assert() calls to their explicit PHPUnit assert alternative', [new CodeSample('assert($value === 100, "message");', '$this->assertSame(100, $value, "message");')]);
    }
    /**
     * @return class-string[]
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     * @return \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall|null
     */
    public function refactor(Node $node)
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isName($node, 'assert')) {
            return null;
        }
        if (!$this->isTestFilePath($node) && !$this->isBehatContext($node)) {
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
        return $this->createCall($node, $methodName, $exprs);
    }
    private function isBehatContext(FuncCall $funcCall) : bool
    {
        $scope = ScopeFetcher::fetch($funcCall);
        if (!$scope->getClassReflection() instanceof ClassReflection) {
            return \false;
        }
        $className = $scope->getClassReflection()->getName();
        // special case with static call
        return \substr_compare($className, 'Context', -\strlen('Context')) === 0;
    }
    private function isTestFilePath(FuncCall $funcCall) : bool
    {
        $scope = ScopeFetcher::fetch($funcCall);
        if (!$scope->getClassReflection() instanceof ClassReflection) {
            return \false;
        }
        $className = $scope->getClassReflection()->getName();
        if (\substr_compare($className, 'Test', -\strlen('Test')) === 0) {
            return \true;
        }
        return \substr_compare($className, 'TestCase', -\strlen('TestCase')) === 0;
    }
    /**
     * @param Expr[] $exprs
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall
     */
    private function createCall(FuncCall $funcCall, string $methodName, array $exprs)
    {
        $args = [];
        foreach ($exprs as $expr) {
            $args[] = new Arg($expr);
        }
        if ($this->isBehatContext($funcCall)) {
            $assertFullyQualified = new FullyQualified(PHPUnitClassName::ASSERT);
            return new StaticCall($assertFullyQualified, $methodName, $args);
        }
        return new MethodCall(new Variable('this'), $methodName, $args);
    }
}
