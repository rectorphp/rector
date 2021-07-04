<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Comparing;

use PhpParser\Node;
use Rector\Comments\CommentRemover;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class NodeComparator
{
    public function __construct(
        private CommentRemover $commentRemover,
        private BetterStandardPrinter $betterStandardPrinter
    ) {
    }

    /**
     * Removes all comments from both nodes
     * @param Node|Node[]|null $node
     */
    public function printWithoutComments(Node | array | null $node): string
    {
        $node = $this->commentRemover->removeFromNode($node);
        $content = $this->betterStandardPrinter->print($node);

        return trim($content);
    }

    /**
     * @param Node|Node[]|null $firstNode
     * @param Node|Node[]|null $secondNode
     */
    public function areNodesEqual(Node | array | null $firstNode, Node | array | null $secondNode): bool
    {
        return $this->printWithoutComments($firstNode) === $this->printWithoutComments($secondNode);
    }

    /**
     * @param Node[] $availableNodes
     */
    public function isNodeEqual(Node $singleNode, array $availableNodes): bool
    {
        // remove comments, only content is relevant
        $singleNode = clone $singleNode;
        $singleNode->setAttribute(AttributeKey::COMMENTS, null);

        foreach ($availableNodes as $availableNode) {
            // remove comments, only content is relevant
            $availableNode = clone $availableNode;
            $availableNode->setAttribute(AttributeKey::COMMENTS, null);

            if ($this->areNodesEqual($singleNode, $availableNode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks even clone nodes
     */
    public function areSameNode(Node $firstNode, Node $secondNode): bool
    {
        if ($firstNode === $secondNode) {
            return true;
        }

        if ($firstNode->getStartTokenPos() !== $secondNode->getStartTokenPos()) {
            return false;
        }

        if ($firstNode->getEndTokenPos() !== $secondNode->getEndTokenPos()) {
            return false;
        }

        $firstClass = $firstNode::class;
        $secondClass = $secondNode::class;

        return $firstClass === $secondClass;
    }
}
