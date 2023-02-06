<?php

declare (strict_types=1);
namespace Rector\Testing\PHPUnit\Behavior;

use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\PhpParser\Printer\NodesWithFileDestinationPrinter;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
/**
 * @property-read RemovedAndAddedFilesCollector $removedAndAddedFilesCollector
 */
trait MovingFilesTrait
{
    protected function assertFileWasAdded(string $expectedFilePath, string $expectedFileContents) : void
    {
        $addedFilePathsWithContents = $this->resolveAddedFilePathsWithContents();
        $wasFound = \false;
        foreach ($addedFilePathsWithContents as $addedFilePathsWithContent) {
            if ($addedFilePathsWithContent->getFilePath() !== $expectedFilePath) {
                continue;
            }
            $this->assertSame($expectedFileContents, $addedFilePathsWithContent->getFileContent());
            $wasFound = \true;
        }
        if ($wasFound === \false) {
            $this->fail(\sprintf('File "%s" was not added', $expectedFilePath));
        }
    }
    /**
     * @return AddedFileWithContent[]
     */
    private function resolveAddedFilePathsWithContents() : array
    {
        $addedFilePathsWithContents = $this->removedAndAddedFilesCollector->getAddedFilesWithContent();
        $nodesWithFileDestinationPrinter = $this->getService(NodesWithFileDestinationPrinter::class);
        $movedFiles = $this->removedAndAddedFilesCollector->getMovedFiles();
        foreach ($movedFiles as $movedFile) {
            $fileContent = $nodesWithFileDestinationPrinter->printNodesWithFileDestination($movedFile);
            $addedFilePathsWithContents[] = new AddedFileWithContent($movedFile->getNewFilePath(), $fileContent);
        }
        $addedFilesWithNodes = $this->removedAndAddedFilesCollector->getAddedFilesWithNodes();
        if ($addedFilesWithNodes === []) {
            return $addedFilePathsWithContents;
        }
        foreach ($addedFilesWithNodes as $addedFileWithNodes) {
            $fileContent = $nodesWithFileDestinationPrinter->printNodesWithFileDestination($addedFileWithNodes);
            $addedFilePathsWithContents[] = new AddedFileWithContent($addedFileWithNodes->getFilePath(), $fileContent);
        }
        return $addedFilePathsWithContents;
    }
}
