<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\NodeAnalyser;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use Rector\PHPUnit\CodeQuality\ValueObject\VariableAndDimFetch;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
final class AssertHasKeyMatcher
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function match(Expression $expression): ?VariableAndDimFetch
    {
        if (!$expression->expr instanceof StaticCall && !$expression->expr instanceof MethodCall) {
            return null;
        }
        if (!$this->testsNodeAnalyzer->isAssertMethodCallName($expression->expr, 'assertArrayHasKey')) {
            return null;
        }
        $assertHasKeyCall = $expression->expr;
        $assertedArg = $assertHasKeyCall->getArgs()[0];
        $assertedExpr = $assertedArg->value;
        if (!$assertedExpr instanceof String_) {
            return null;
        }
        $variableArg = $assertHasKeyCall->getArgs()[1];
        $variableExpr = $variableArg->value;
        if (!$variableExpr instanceof Variable) {
            return null;
        }
        return new VariableAndDimFetch($variableExpr, $assertedExpr);
    }
}
