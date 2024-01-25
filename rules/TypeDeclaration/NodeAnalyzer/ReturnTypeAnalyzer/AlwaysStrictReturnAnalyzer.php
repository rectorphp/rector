<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer\ReturnTypeAnalyzer;

use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Expr\YieldFrom;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer;
final class AlwaysStrictReturnAnalyzer
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer
     */
    private $returnAnalyzer;
    public function __construct(BetterNodeFinder $betterNodeFinder, ReturnAnalyzer $returnAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->returnAnalyzer = $returnAnalyzer;
    }
    /**
     * @return Return_[]
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function matchAlwaysStrictReturns($functionLike) : array
    {
        if ($functionLike->stmts === null) {
            return [];
        }
        if ($this->betterNodeFinder->hasInstancesOfInFunctionLikeScoped($functionLike, [Yield_::class, YieldFrom::class])) {
            return [];
        }
        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->findInstancesOfInFunctionLikeScoped($functionLike, Return_::class);
        if ($returns === []) {
            return [];
        }
        // is one statement depth 3?
        if (!$this->returnAnalyzer->areExclusiveExprReturns($returns)) {
            return [];
        }
        // is one in ifOrElse, other in else?
        if ($this->hasOnlyStmtWithIfAndElse($functionLike)) {
            return $returns;
        }
        // has root return?
        if (!$this->returnAnalyzer->hasClassMethodRootReturn($functionLike)) {
            return [];
        }
        return $returns;
    }
    /**
     * @param \PhpParser\Node\Stmt\If_|\PhpParser\Node\Stmt\Else_ $ifOrElse
     */
    private function hasFirstLevelReturn($ifOrElse) : bool
    {
        foreach ($ifOrElse->stmts as $stmt) {
            if ($stmt instanceof Return_) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    private function hasOnlyStmtWithIfAndElse($functionLike) : bool
    {
        foreach ((array) $functionLike->stmts as $functionLikeStmt) {
            if (!$functionLikeStmt instanceof If_) {
                continue;
            }
            $if = $functionLikeStmt;
            if ($if->elseifs !== []) {
                return \false;
            }
            if (!$if->else instanceof Else_) {
                return \false;
            }
            if (!$this->hasFirstLevelReturn($if)) {
                return \false;
            }
            return $this->hasFirstLevelReturn($if->else);
        }
        return \false;
    }
}
