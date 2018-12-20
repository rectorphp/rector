<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
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
     * @required
     */
    public function setCallAnalyzer(BetterStandardPrinter $betterStandardPrinter): void
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    /**
     * @param Node|Node[] $firstNode
     * @param Node|Node[] $secondNode
     */
    public function areNodesEqual($firstNode, $secondNode): bool
    {
        return $this->print($firstNode) === $this->print($secondNode);
    }

    /**
     * @param Node|Node[]|null $node
     */
    public function print($node): string
    {
        if ($node === null) {
            $node = [];
        }

        if (! is_array($node)) {
            $node = [$node];
        }

        return $this->betterStandardPrinter->prettyPrint($node);
    }
}
