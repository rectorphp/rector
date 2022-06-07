<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\Printer;

use Rector\Core\Contract\PhpParser\NodePrinterInterface;
use Rector\FileSystemRector\Contract\FileWithNodesInterface;
use Rector\PostRector\Application\PostFileProcessor;
final class NodesWithFileDestinationPrinter
{
    /**
     * @readonly
     * @var \Rector\Core\Contract\PhpParser\NodePrinterInterface
     */
    private $nodePrinter;
    /**
     * @readonly
     * @var \Rector\PostRector\Application\PostFileProcessor
     */
    private $postFileProcessor;
    public function __construct(NodePrinterInterface $nodePrinter, PostFileProcessor $postFileProcessor)
    {
        $this->nodePrinter = $nodePrinter;
        $this->postFileProcessor = $postFileProcessor;
    }
    public function printNodesWithFileDestination(FileWithNodesInterface $fileWithNodes) : string
    {
        $nodes = $this->postFileProcessor->traverse($fileWithNodes->getNodes());
        return $this->nodePrinter->prettyPrintFile($nodes);
    }
}
