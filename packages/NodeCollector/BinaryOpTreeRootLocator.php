<?php

declare(strict_types=1);

namespace Rector\NodeCollector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Tests\NodeCollector\BinaryOpTreeRootLocatorTest
 */
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
    public function findOperationRoot(Expr $expr, string $binaryOpClass): Expr
    {
        $parentNode = $expr->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof Node) {
            return $expr;
        }

        if ($parentNode::class !== $binaryOpClass) {
            return $expr;
        }

        if (! $parentNode instanceof BinaryOp) {
            return $expr;
        }

        if ($parentNode->left === $expr) {
            return $this->findOperationRoot($parentNode, $binaryOpClass);
        }

        $isRightChild = $parentNode->right === $expr;
        if (! $isRightChild) {
            return $expr;
        }

        if ($expr::class === $binaryOpClass) {
            return $expr;
        }

        return $this->findOperationRoot($parentNode, $binaryOpClass);
    }
}
