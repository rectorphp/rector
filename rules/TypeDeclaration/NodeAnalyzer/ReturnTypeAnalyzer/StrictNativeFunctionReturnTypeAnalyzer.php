<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\NodeAnalyzer\ReturnTypeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\TypeDeclaration\NodeAnalyzer\ReturnFilter\ExclusiveNativeFuncCallReturnMatcher;

final class StrictNativeFunctionReturnTypeAnalyzer
{
    public function __construct(
        private readonly BetterNodeFinder $betterNodeFinder,
        private readonly ExclusiveNativeFuncCallReturnMatcher $exclusiveNativeFuncCallReturnMatcher
    ) {
    }

    /**
     * @return FuncCall[]|null
     */
    public function matchAlwaysReturnNativeFuncCalls(ClassMethod|Closure|Function_ $functionLike): ?array
    {
        if ($functionLike->stmts === null) {
            return null;
        }

        if ($this->betterNodeFinder->hasInstancesOfInFunctionLikeScoped($functionLike, [Yield_::class])) {
            return null;
        }

        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->findInstancesOfInFunctionLikeScoped($functionLike, Return_::class);
        if ($returns === []) {
            return null;
        }

        // is one statement depth 3?
        if (! $this->areExclusiveExprReturns($returns)) {
            return null;
        }

        // has root return?
        if (! $this->hasClassMethodRootReturn($functionLike)) {
            return null;
        }

        $nativeFuncCalls = $this->exclusiveNativeFuncCallReturnMatcher->match($returns);
        if ($nativeFuncCalls === null) {
            return null;
        }

        return $nativeFuncCalls;
    }

    /**
     * @param Return_[] $returns
     */
    private function areExclusiveExprReturns(array $returns): bool
    {
        foreach ($returns as $return) {
            if (! $return->expr instanceof Expr) {
                return false;
            }
        }

        return true;
    }

    private function hasClassMethodRootReturn(ClassMethod|Function_|Closure $functionLike): bool
    {
        foreach ((array) $functionLike->stmts as $stmt) {
            if ($stmt instanceof Return_) {
                return true;
            }
        }

        return false;
    }
}
