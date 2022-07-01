<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer\ReturnTypeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\TypeDeclaration\TypeAnalyzer\AlwaysStrictBoolExprAnalyzer;
final class StrictBoolReturnTypeAnalyzer
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeAnalyzer\AlwaysStrictBoolExprAnalyzer
     */
    private $alwaysStrictBoolExprAnalyzer;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\ReturnTypeAnalyzer\AlwaysStrictReturnAnalyzer
     */
    private $alwaysStrictReturnAnalyzer;
    public function __construct(AlwaysStrictBoolExprAnalyzer $alwaysStrictBoolExprAnalyzer, \Rector\TypeDeclaration\NodeAnalyzer\ReturnTypeAnalyzer\AlwaysStrictReturnAnalyzer $alwaysStrictReturnAnalyzer)
    {
        $this->alwaysStrictBoolExprAnalyzer = $alwaysStrictBoolExprAnalyzer;
        $this->alwaysStrictReturnAnalyzer = $alwaysStrictReturnAnalyzer;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function hasAlwaysStrictBoolReturn($functionLike) : bool
    {
        $returns = $this->alwaysStrictReturnAnalyzer->matchAlwaysStrictReturns($functionLike);
        if ($returns === null) {
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
}
