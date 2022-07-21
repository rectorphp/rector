<?php

declare (strict_types=1);
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
     * @api
     * @param class-string<BinaryOp> $binaryOpClass
     */
    public function findOperationRoot(Expr $expr, string $binaryOpClass) : Expr
    {
        $parentNode = $expr->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof Node) {
            // No more parents so the Expr node must be root.
            return $expr;
        }
        if (\get_class($parentNode) !== $binaryOpClass) {
            // If the Expr node is not a child of the desired operation,
            // it must already be the root of the operation tree.
            return $expr;
        }
        /** @var BinaryOp $parentNode */
        $isParentARightAssociativeTree = $parentNode->right === $expr && \get_class($expr) === $binaryOpClass;
        if ($isParentARightAssociativeTree) {
            // The Expr node is the right child of its parent but it is the desired operation (BinaryOp b c).
            // Since the AST fragment (BinaryOp a >(BinaryOp b c)<) corresponds to a right-associative,
            // the current node must be the root of a left-associative tree.
            return $expr;
        }
        // We already know the parent is the desired operation and the current node is not its right child.
        // This means the parent is a root of a larger left-associative tree so we continue recursively.
        return $this->findOperationRoot($parentNode, $binaryOpClass);
    }
}
