<?php

declare(strict_types=1);

namespace Rector\NetteCodeQuality\NodeAnalyzer;

use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\FuncCall;
use Rector\NetteCodeQuality\ValueObject\FuncCallAndExpr;
use Rector\NodeNameResolver\NodeNameResolver;

final class BinaryOpAnalyzer
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function matchFuncCallAndOtherExpr(BinaryOp $binaryOp, string $funcCallName): ?FuncCallAndExpr
    {
        if ($binaryOp->left instanceof FuncCall) {
            if (! $this->nodeNameResolver->isName($binaryOp->left, $funcCallName)) {
                return null;
            }

            return new FuncCallAndExpr($binaryOp->left, $binaryOp->right);
        }

        if ($binaryOp->right instanceof FuncCall) {
            if (! $this->nodeNameResolver->isName($binaryOp->right, $funcCallName)) {
                return null;
            }

            return new FuncCallAndExpr($binaryOp->right, $binaryOp->left);
        }

        return null;
    }
}
