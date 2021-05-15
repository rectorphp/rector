<?php

declare (strict_types=1);
namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\FuncCall;
use Rector\Nette\ValueObject\FuncCallAndExpr;
use Rector\NodeNameResolver\NodeNameResolver;
final class BinaryOpAnalyzer
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function matchFuncCallAndOtherExpr(\PhpParser\Node\Expr\BinaryOp $binaryOp, string $funcCallName) : ?\Rector\Nette\ValueObject\FuncCallAndExpr
    {
        if ($binaryOp->left instanceof \PhpParser\Node\Expr\FuncCall) {
            if (!$this->nodeNameResolver->isName($binaryOp->left, $funcCallName)) {
                return null;
            }
            return new \Rector\Nette\ValueObject\FuncCallAndExpr($binaryOp->left, $binaryOp->right);
        }
        if ($binaryOp->right instanceof \PhpParser\Node\Expr\FuncCall) {
            if (!$this->nodeNameResolver->isName($binaryOp->right, $funcCallName)) {
                return null;
            }
            return new \Rector\Nette\ValueObject\FuncCallAndExpr($binaryOp->right, $binaryOp->left);
        }
        return null;
    }
}
