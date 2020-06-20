<?php

declare(strict_types=1);

namespace Rector\Core\Rector\AbstractRector;

use PhpParser\Node;
use Rector\Autodiscovery\ValueObject\NodesWithFileDestination;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 *
 * @property BetterStandardPrinter $betterStandardPrinter
 */
trait RemovedAndAddedFilesTrait
{
    /**
     * @var RemovedAndAddedFilesCollector
     */
    private $removedAndAddedFilesCollector;

    /**
     * @required
     */
    public function autowireRemovedAndAddedFilesTrait(
        RemovedAndAddedFilesCollector $removedAndAddedFilesCollector
    ): void {
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
    }

    /**
     * @param Node|Node[]|null $node
     */
    protected function printNodesToFilePath($node, string $fileLocation): void
    {
        $eventContent = $this->betterStandardPrinter->prettyPrintFile($node);
        $this->removedAndAddedFilesCollector->addFileWithContent($fileLocation, $eventContent);
    }

    protected function moveFile(SmartFileInfo $oldFileInfo, string $newFileLocation, ?string $fileContent = null): void
    {
        $this->removedAndAddedFilesCollector->addMovedFile($oldFileInfo, $newFileLocation, $fileContent);
    }

    protected function removeFile(SmartFileInfo $smartFileInfo): void
    {
        $this->removedAndAddedFilesCollector->removeFile($smartFileInfo);
    }

    private function addFile(string $filePath, string $content): void
    {
        $this->removedAndAddedFilesCollector->addFileWithContent($filePath, $content);
    }

    private function addNodesWithFileDestination(NodesWithFileDestination $nodesWithFileDestination): void
    {
        $this->removedAndAddedFilesCollector->addNodesWithFileDestination($nodesWithFileDestination);
    }
}
