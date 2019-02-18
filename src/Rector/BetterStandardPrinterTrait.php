<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Printer\BetterStandardPrinter;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait BetterStandardPrinterTrait
{
    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @required
     */
    public function autowireBetterStandardPrinter(
        BetterStandardPrinter $betterStandardPrinter,
        BetterNodeFinder $betterNodeFinder
    ): void {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->betterNodeFinder = $betterNodeFinder;
    }

    /**
     * @param Node|Node[]|null $node
     */
    public function print($node): string
    {
        return $this->betterStandardPrinter->print($node);
    }

    /**
     * @param Node|Node[]|null $firstNode
     * @param Node|Node[]|null $secondNode
     */
    protected function areNodesEqual($firstNode, $secondNode): bool
    {
        return $this->betterStandardPrinter->areNodesEqual($firstNode, $secondNode);
    }

    /**
     * @param Node[] $availableNodes
     */
    protected function isNodeEqual(Node $singleNode, array $availableNodes): bool
    {
        // remove comments, only content is relevant
        $singleNode = clone $singleNode;
        $singleNode->setAttribute('comments', null);

        foreach ($availableNodes as $availableNode) {
            // remove comments, only content is relevant
            $availableNode = clone $availableNode;
            $availableNode->setAttribute('comments', null);

            if ($this->areNodesEqual($singleNode, $availableNode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Node|Node[] $nodes
     */
    protected function isNodeUsedIn(Node $seekedNode, $nodes): bool
    {
        return (bool) $this->betterNodeFinder->findFirst($nodes, function (Node $node) use ($seekedNode) {
            return $this->areNodesEqual($node, $seekedNode);
        });
    }
}
