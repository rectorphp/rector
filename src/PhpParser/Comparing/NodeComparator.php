<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\Comparing;

use PhpParser\Node;
use Rector\Comments\CommentRemover;
use Rector\Core\Contract\PhpParser\NodePrinterInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class NodeComparator
{
    /**
     * @readonly
     * @var \Rector\Comments\CommentRemover
     */
    private $commentRemover;
    /**
     * @readonly
     * @var \Rector\Core\Contract\PhpParser\NodePrinterInterface
     */
    private $nodePrinter;
    public function __construct(\Rector\Comments\CommentRemover $commentRemover, \Rector\Core\Contract\PhpParser\NodePrinterInterface $nodePrinter)
    {
        $this->commentRemover = $commentRemover;
        $this->nodePrinter = $nodePrinter;
    }
    /**
     * Removes all comments from both nodes
     * @param \PhpParser\Node|mixed[]|null $node
     */
    public function printWithoutComments($node) : string
    {
        $node = $this->commentRemover->removeFromNode($node);
        $content = $this->nodePrinter->print($node);
        return \trim($content);
    }
    /**
     * @param \PhpParser\Node|mixed[]|null $firstNode
     * @param \PhpParser\Node|mixed[]|null $secondNode
     */
    public function areNodesEqual($firstNode, $secondNode) : bool
    {
        return $this->printWithoutComments($firstNode) === $this->printWithoutComments($secondNode);
    }
    /**
     * @param Node[] $availableNodes
     */
    public function isNodeEqual(\PhpParser\Node $singleNode, array $availableNodes) : bool
    {
        // remove comments, only content is relevant
        $singleNode = clone $singleNode;
        $singleNode->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS, null);
        foreach ($availableNodes as $availableNode) {
            // remove comments, only content is relevant
            $availableNode = clone $availableNode;
            $availableNode->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS, null);
            if ($this->areNodesEqual($singleNode, $availableNode)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * Checks even clone nodes
     */
    public function areSameNode(\PhpParser\Node $firstNode, \PhpParser\Node $secondNode) : bool
    {
        if ($firstNode === $secondNode) {
            return \true;
        }
        if ($firstNode->getStartTokenPos() !== $secondNode->getStartTokenPos()) {
            return \false;
        }
        if ($firstNode->getEndTokenPos() !== $secondNode->getEndTokenPos()) {
            return \false;
        }
        $firstClass = \get_class($firstNode);
        $secondClass = \get_class($secondNode);
        return $firstClass === $secondClass;
    }
}
