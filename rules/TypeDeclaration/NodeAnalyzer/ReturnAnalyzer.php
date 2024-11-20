<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use Rector\TypeDeclaration\TypeInferer\SilentVoidResolver;
final class ReturnAnalyzer
{
    /**
     * @readonly
     */
    private SilentVoidResolver $silentVoidResolver;
    public function __construct(SilentVoidResolver $silentVoidResolver)
    {
        $this->silentVoidResolver = $silentVoidResolver;
    }
    /**
     * @param Return_[] $returns
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function hasOnlyReturnWithExpr($functionLike, array $returns) : bool
    {
        if ($functionLike->stmts === null) {
            return \false;
        }
        // void or combined with yield/yield from
        if ($returns === []) {
            return \false;
        }
        // possible void
        foreach ($returns as $return) {
            if (!$return->expr instanceof Expr) {
                return \false;
            }
        }
        // possible silent void
        return !$this->silentVoidResolver->hasSilentVoid($functionLike);
    }
}
