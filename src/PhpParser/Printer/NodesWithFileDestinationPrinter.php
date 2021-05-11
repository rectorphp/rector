<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Printer;

use Rector\FileSystemRector\Contract\FileWithNodesInterface;
use Rector\PostRector\Application\PostFileProcessor;

final class NodesWithFileDestinationPrinter
{
    public function __construct(
        private BetterStandardPrinter $betterStandardPrinter,
        private PostFileProcessor $postFileProcessor
    ) {
    }

    public function printNodesWithFileDestination(FileWithNodesInterface $fileWithNodes): string
    {
        $nodes = $this->postFileProcessor->traverse($fileWithNodes->getNodes());
        return $this->betterStandardPrinter->prettyPrintFile($nodes);
    }
}
