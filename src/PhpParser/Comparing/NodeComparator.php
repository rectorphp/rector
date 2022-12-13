<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\Comparing;

use PhpParser\Node;
use Rector\Comments\CommentRemover;
use Rector\Core\Contract\PhpParser\NodePrinterInterface;
use RectorPrefix202212\Webmozart\Assert\Assert;
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
    public function __construct(CommentRemover $commentRemover, NodePrinterInterface $nodePrinter)
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
        if ($firstNode instanceof Node && $secondNode === null) {
            return \false;
        }
        if ($secondNode instanceof Node && $firstNode === null) {
            return \false;
        }
        if (\is_array($firstNode)) {
            Assert::allIsAOf($firstNode, Node::class);
            if ($secondNode === null) {
                return \false;
            }
        }
        if (\is_array($secondNode)) {
            Assert::allIsAOf($secondNode, Node::class);
            if ($firstNode === null) {
                return \false;
            }
        }
        return $this->printWithoutComments($firstNode) === $this->printWithoutComments($secondNode);
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
        $printFirstNode = $this->nodePrinter->print($firstNode);
        $printSecondNode = $this->nodePrinter->print($secondNode);
        return $printFirstNode === $printSecondNode;
    }
}
