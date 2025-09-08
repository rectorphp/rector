<?php

declare (strict_types=1);
namespace Rector\Php80\NodeResolver;

use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\FuncCall;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpParser\Node\Value\ValueResolver;
final class StrFalseComparisonResolver
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(ValueResolver $valueResolver, NodeNameResolver $nodeNameResolver)
    {
        $this->valueResolver = $valueResolver;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param string[] $oldStrFuncNames
     * @param \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\NotIdentical|\PhpParser\Node\Expr\BinaryOp\Equal|\PhpParser\Node\Expr\BinaryOp\NotEqual $expr
     */
    public function resolve($expr, array $oldStrFuncNames): ?FuncCall
    {
        if ($this->valueResolver->isFalse($expr->left)) {
            if (!$expr->right instanceof FuncCall) {
                return null;
            }
            if (!$this->nodeNameResolver->isNames($expr->right, $oldStrFuncNames)) {
                return null;
            }
            /** @var FuncCall $funcCall */
            $funcCall = $expr->right;
            return $funcCall;
        }
        if ($this->valueResolver->isFalse($expr->right)) {
            if (!$expr->left instanceof FuncCall) {
                return null;
            }
            if (!$this->nodeNameResolver->isNames($expr->left, $oldStrFuncNames)) {
                return null;
            }
            /** @var FuncCall $funcCall */
            $funcCall = $expr->left;
            return $funcCall;
        }
        return null;
    }
}
