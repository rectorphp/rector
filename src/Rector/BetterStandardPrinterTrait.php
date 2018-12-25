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
     * @param Node|Node[] $firstNode
     * @param Node|Node[] $secondNode
     */
    protected function areNodesEqual($firstNode, $secondNode): bool
    {
        return $this->print($firstNode) === $this->print($secondNode);
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
