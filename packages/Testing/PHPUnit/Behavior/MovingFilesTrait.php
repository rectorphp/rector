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
        foreach ($addedFilePathsWithContents as $addedFilePathWithContent) {
            if ($addedFilePathWithContent->getFilePath() !== $expectedFilePath) {
                continue;
            }
            $this->assertSame($expectedFileContents, $addedFilePathWithContent->getFileContent());
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
        $addedFilesWithNodes = $this->removedAndAddedFilesCollector->getAddedFilesWithNodes();
        if ($addedFilesWithNodes === []) {
            return $addedFilePathsWithContents;
        }
        foreach ($addedFilesWithNodes as $addedFileWithNode) {
            $fileContent = $nodesWithFileDestinationPrinter->printNodesWithFileDestination($addedFileWithNode);
            $addedFilePathsWithContents[] = new AddedFileWithContent($addedFileWithNode->getFilePath(), $fileContent);
        }
        return $addedFilePathsWithContents;
    }
}
