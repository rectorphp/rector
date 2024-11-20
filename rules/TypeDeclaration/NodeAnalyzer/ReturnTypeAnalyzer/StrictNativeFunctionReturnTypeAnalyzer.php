<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer\ReturnTypeAnalyzer;

use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer;
use Rector\TypeDeclaration\NodeAnalyzer\ReturnFilter\ExclusiveNativeCallLikeReturnMatcher;
final class StrictNativeFunctionReturnTypeAnalyzer
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private ExclusiveNativeCallLikeReturnMatcher $exclusiveNativeCallLikeReturnMatcher;
    /**
     * @readonly
     */
    private ReturnAnalyzer $returnAnalyzer;
    public function __construct(BetterNodeFinder $betterNodeFinder, ExclusiveNativeCallLikeReturnMatcher $exclusiveNativeCallLikeReturnMatcher, ReturnAnalyzer $returnAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->exclusiveNativeCallLikeReturnMatcher = $exclusiveNativeCallLikeReturnMatcher;
        $this->returnAnalyzer = $returnAnalyzer;
    }
    /**
     * @return CallLike[]|null
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function matchAlwaysReturnNativeCallLikes($functionLike) : ?array
    {
        if ($functionLike->stmts === null) {
            return null;
        }
        $returns = $this->betterNodeFinder->findReturnsScoped($functionLike);
        if (!$this->returnAnalyzer->hasOnlyReturnWithExpr($functionLike, $returns)) {
            return null;
        }
        return $this->exclusiveNativeCallLikeReturnMatcher->match($returns);
    }
}
