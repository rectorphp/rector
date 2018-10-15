<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use Rector\Printer\BetterStandardPrinter;

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
        $firstContent = $this->betterStandardPrinter->prettyPrint(is_array($firstNode) ? $firstNode : [$firstNode]);
        $secondContent = $this->betterStandardPrinter->prettyPrint(is_array($secondNode) ? $secondNode : [$secondNode]);

        return $firstContent === $secondContent;
    }
}
