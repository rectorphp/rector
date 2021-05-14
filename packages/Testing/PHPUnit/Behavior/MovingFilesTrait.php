<?php

declare (strict_types=1);
namespace Rector\Testing\PHPUnit\Behavior;

use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\PhpParser\Printer\NodesWithFileDestinationPrinter;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix20210514\Webmozart\Assert\Assert;
/**
 * @property-read RemovedAndAddedFilesCollector $removedAndAddedFilesCollector
 */
trait MovingFilesTrait
{
    protected function assertFileWasNotChanged(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : void
    {
        $hasFileInfo = $this->removedAndAddedFilesCollector->isFileRemoved($smartFileInfo);
        $this->assertFalse($hasFileInfo);
    }
    protected function assertFileWasAdded(\Rector\FileSystemRector\ValueObject\AddedFileWithContent $addedFileWithContent) : void
    {
        $this->assertFilesWereAdded([$addedFileWithContent]);
    }
    protected function assertFileWasRemoved(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : void
    {
        $isFileRemoved = $this->removedAndAddedFilesCollector->isFileRemoved($smartFileInfo);
        $this->assertTrue($isFileRemoved);
    }
    /**
     * @param AddedFileWithContent[] $expectedAddedFileWithContents
     */
    protected function assertFilesWereAdded(array $expectedAddedFileWithContents) : void
    {
        \RectorPrefix20210514\Webmozart\Assert\Assert::allIsAOf($expectedAddedFileWithContents, \Rector\FileSystemRector\ValueObject\AddedFileWithContent::class);
        \sort($expectedAddedFileWithContents);
        $addedFilePathsWithContents = $this->resolveAddedFilePathsWithContents();
        \sort($addedFilePathsWithContents);
        // there should be at least some added files
        \RectorPrefix20210514\Webmozart\Assert\Assert::notEmpty($addedFilePathsWithContents);
        foreach ($addedFilePathsWithContents as $key => $addedFilePathWithContent) {
            $expectedFilePathWithContent = $expectedAddedFileWithContents[$key];
            $this->assertSame($expectedFilePathWithContent->getFilePath(), $addedFilePathWithContent->getFilePath());
            $this->assertSame($expectedFilePathWithContent->getFileContent(), $addedFilePathWithContent->getFileContent());
        }
    }
    /**
     * @return AddedFileWithContent[]
     */
    private function resolveAddedFilePathsWithContents() : array
    {
        $addedFilePathsWithContents = $this->removedAndAddedFilesCollector->getAddedFilesWithContent();
        $addedFilesWithNodes = $this->removedAndAddedFilesCollector->getAddedFilesWithNodes();
        foreach ($addedFilesWithNodes as $addedFileWithNode) {
            $nodesWithFileDestinationPrinter = $this->getService(\Rector\Core\PhpParser\Printer\NodesWithFileDestinationPrinter::class);
            $fileContent = $nodesWithFileDestinationPrinter->printNodesWithFileDestination($addedFileWithNode);
            $addedFilePathsWithContents[] = new \Rector\FileSystemRector\ValueObject\AddedFileWithContent($addedFileWithNode->getFilePath(), $fileContent);
        }
        return $addedFilePathsWithContents;
    }
}
