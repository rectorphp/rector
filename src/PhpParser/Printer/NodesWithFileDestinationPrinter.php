<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\Printer;

use Rector\FileSystemRector\ValueObject\AddedFileWithNodes;
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
    public function __construct(\Rector\Core\PhpParser\Printer\BetterStandardPrinter $betterStandardPrinter, PostFileProcessor $postFileProcessor)
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->postFileProcessor = $postFileProcessor;
    }
    public function printNodesWithFileDestination(AddedFileWithNodes $addedFileWithNodes) : string
    {
        $nodes = $this->postFileProcessor->traverse($addedFileWithNodes->getNodes());
        return $this->betterStandardPrinter->prettyPrintFile($nodes);
    }
}
