<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer\ReturnTypeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\TypeDeclaration\TypeAnalyzer\AlwaysStrictBoolExprAnalyzer;
final class StrictBoolReturnTypeAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeAnalyzer\AlwaysStrictBoolExprAnalyzer
     */
    private $alwaysStrictBoolExprAnalyzer;
    public function __construct(BetterNodeFinder $betterNodeFinder, AlwaysStrictBoolExprAnalyzer $alwaysStrictBoolExprAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->alwaysStrictBoolExprAnalyzer = $alwaysStrictBoolExprAnalyzer;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function hasAlwaysStrictBoolReturn($functionLike) : bool
    {
        if ($functionLike->stmts === null) {
            return \false;
        }
        if ($this->betterNodeFinder->hasInstancesOfInFunctionLikeScoped($functionLike, [Yield_::class])) {
            return \false;
        }
        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->findInstancesOfInFunctionLikeScoped($functionLike, Return_::class);
        if ($returns === []) {
            return \false;
        }
        // is one statement depth 3?
        if (!$this->areExclusiveExprReturns($returns)) {
            return \false;
        }
        // has root return?
        if (!$this->hasClassMethodRootReturn($functionLike)) {
            return \false;
        }
        foreach ($returns as $return) {
            // we need exact expr return
            if (!$return->expr instanceof Expr) {
                return \false;
            }
            if (!$this->alwaysStrictBoolExprAnalyzer->isStrictBoolExpr($return->expr)) {
                return \false;
            }
        }
        return \true;
    }
    /**
     * @param Return_[] $returns
     */
    private function areExclusiveExprReturns(array $returns) : bool
    {
        foreach ($returns as $return) {
            if (!$return->expr instanceof Expr) {
                return \false;
            }
        }
        return \true;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    private function hasClassMethodRootReturn($functionLike) : bool
    {
        foreach ((array) $functionLike->stmts as $stmt) {
            if ($stmt instanceof Return_) {
                return \true;
            }
        }
        return \false;
    }
}
