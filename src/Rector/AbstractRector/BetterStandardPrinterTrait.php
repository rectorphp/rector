<?php

declare(strict_types=1);

namespace Rector\Core\Rector\AbstractRector;

use PhpParser\Node;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait BetterStandardPrinterTrait
{
    /**
     * @var BetterStandardPrinter
     */
    protected $betterStandardPrinter;

    /**
     * @required
     */
    public function autowireBetterStandardPrinterTrait(BetterStandardPrinter $betterStandardPrinter): void
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    /**
     * @param Node|Node[]|null $node
     */
    public function print($node): string
    {
        return $this->betterStandardPrinter->print($node);
    }

    /**
     * Removes all comments from both nodes
     *
     * @param Node|Node[]|null $firstNode
     * @param Node|Node[]|null $secondNode
     */
    protected function areNodesEqual($firstNode, $secondNode): bool
    {
        return $this->betterStandardPrinter->areNodesEqual($firstNode, $secondNode);
    }
}
