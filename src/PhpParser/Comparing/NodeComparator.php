<?php

declare (strict_types=1);
namespace Rector\PhpParser\Comparing;

use PhpParser\Node;
use Rector\Comments\CommentRemover;
use Rector\PhpParser\Printer\BetterStandardPrinter;
final class NodeComparator
{
    /**
     * @readonly
     */
    private CommentRemover $commentRemover;
    /**
     * @readonly
     */
    private BetterStandardPrinter $betterStandardPrinter;
    public function __construct(CommentRemover $commentRemover, BetterStandardPrinter $betterStandardPrinter)
    {
        $this->commentRemover = $commentRemover;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }
    /**
     * Removes all comments from both nodes
     * @param Node|Node[]|null $node
     */
    public function printWithoutComments($node) : string
    {
        $node = $this->commentRemover->removeFromNode($node);
        $content = $this->betterStandardPrinter->print($node);
        return \trim($content);
    }
    /**
     * @param Node|Node[]|null $firstNode
     * @param Node|Node[]|null $secondNode
     */
    public function areNodesEqual($firstNode, $secondNode) : bool
    {
        if ($firstNode instanceof Node && !$secondNode instanceof Node) {
            return \false;
        }
        if (!$firstNode instanceof Node && $secondNode instanceof Node) {
            return \false;
        }
        if (\is_array($firstNode) && !\is_array($secondNode)) {
            return \false;
        }
        if (!\is_array($secondNode)) {
            return $this->printWithoutComments($firstNode) === $this->printWithoutComments($secondNode);
        }
        if (\is_array($firstNode)) {
            return $this->printWithoutComments($firstNode) === $this->printWithoutComments($secondNode);
        }
        return \false;
    }
    /**
     * @api
     * @param Node[] $availableNodes
     */
    public function isNodeEqual(Node $singleNode, array $availableNodes) : bool
    {
        foreach ($availableNodes as $availableNode) {
            if ($this->areNodesEqual($singleNode, $availableNode)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * Checks even clone nodes
     */
    public function areSameNode(Node $firstNode, Node $secondNode) : bool
    {
        if ($firstNode === $secondNode) {
            return \true;
        }
        $firstClass = \get_class($firstNode);
        $secondClass = \get_class($secondNode);
        if ($firstClass !== $secondClass) {
            return \false;
        }
        if ($firstNode->getStartTokenPos() !== $secondNode->getStartTokenPos()) {
            return \false;
        }
        if ($firstNode->getEndTokenPos() !== $secondNode->getEndTokenPos()) {
            return \false;
        }
        $printFirstNode = $this->betterStandardPrinter->print($firstNode);
        $printSecondNode = $this->betterStandardPrinter->print($secondNode);
        return $printFirstNode === $printSecondNode;
    }
}
