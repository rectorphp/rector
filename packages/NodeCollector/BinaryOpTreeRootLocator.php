<?php

declare (strict_types=1);
namespace Rector\NodeCollector;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class BinaryOpTreeRootLocator
{
    /**
     * Locates the root of a left-associative tree of the given binary operation,
     * which has given expression as one of its leaves.
     *
     * This is useful in conjunction with BinaryOpConditionsCollector, which expects such tree.
     *
     * @param class-string<BinaryOp> $binaryOpClass
     */
    public function findOperationRoot(\PhpParser\Node\Expr $expr, string $binaryOpClass) : \PhpParser\Node\Expr
    {
        /** @var ?Expr $parentNode */
        $parentNode = $expr->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parentNode === null || \get_class($parentNode) !== $binaryOpClass) {
            return $expr;
        }
        \assert($parentNode instanceof \PhpParser\Node\Expr\BinaryOp);
        $isLeftChild = $parentNode->left === $expr;
        $isRightChild = $parentNode->right === $expr;
        $isRightChildAndNotBinaryOp = $isRightChild && \get_class($expr) !== $binaryOpClass;
        if ($isLeftChild || $isRightChildAndNotBinaryOp) {
            return $this->findOperationRoot($parentNode, $binaryOpClass);
        }
        return $expr;
    }
}
