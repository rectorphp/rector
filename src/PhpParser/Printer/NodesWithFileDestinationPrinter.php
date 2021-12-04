<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\Printer;

use Rector\FileSystemRector\Contract\FileWithNodesInterface;
use Rector\PostRector\Application\PostFileProcessor;
final class NodesWithFileDestinationPrinter
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Printer\BetterStandardPrinter
     */
    private $betterStandardPrinter;
    /**
     * @readonly
     * @var \Rector\PostRector\Application\PostFileProcessor
     */
    private $postFileProcessor;
    public function __construct(\Rector\Core\PhpParser\Printer\BetterStandardPrinter $betterStandardPrinter, \Rector\PostRector\Application\PostFileProcessor $postFileProcessor)
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->postFileProcessor = $postFileProcessor;
    }
    public function printNodesWithFileDestination(\Rector\FileSystemRector\Contract\FileWithNodesInterface $fileWithNodes) : string
    {
        $nodes = $this->postFileProcessor->traverse($fileWithNodes->getNodes());
        return $this->betterStandardPrinter->prettyPrintFile($nodes);
    }
}
