<?php

declare (strict_types=1);
namespace Rector\Testing\PHPUnit\Behavior;

use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\PhpParser\Printer\NodesWithFileDestinationPrinter;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix20211231\Webmozart\Assert\Assert;
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
        \RectorPrefix20211231\Webmozart\Assert\Assert::allIsAOf($expectedAddedFileWithContents, \Rector\FileSystemRector\ValueObject\AddedFileWithContent::class);
        \sort($expectedAddedFileWithContents);
        $addedFilePathsWithContents = $this->resolveAddedFilePathsWithContents();
        \sort($addedFilePathsWithContents);
        // there should be at least some added files
        \RectorPrefix20211231\Webmozart\Assert\Assert::notEmpty($addedFilePathsWithContents);
        foreach ($addedFilePathsWithContents as $key => $addedFilePathWithContent) {
            $expectedFilePathWithContent = $expectedAddedFileWithContents[$key];
            /**
             * use relative path against _temp_fixture_easy_testing
             * to make work in all OSs, for example:
             * In MacOS, the realpath() of sys_get_temp_dir() pointed to /private/var/* which symlinked of /var/*
             */
            [, $expectedFilePathWithContentFilePath] = \explode('_temp_fixture_easy_testing', $expectedFilePathWithContent->getFilePath());
            [, $addedFilePathWithContentFilePath] = \explode('_temp_fixture_easy_testing', $addedFilePathWithContent->getFilePath());
            $this->assertSame($expectedFilePathWithContentFilePath, $addedFilePathWithContentFilePath);
            $this->assertSame($expectedFilePathWithContent->getFileContent(), $addedFilePathWithContent->getFileContent());
        }
    }
    /**
     * @return AddedFileWithContent[]
     */
    private function resolveAddedFilePathsWithContents() : array
    {
        $addedFilePathsWithContents = $this->removedAndAddedFilesCollector->getAddedFilesWithContent();
        $nodesWithFileDestinationPrinter = $this->getService(\Rector\Core\PhpParser\Printer\NodesWithFileDestinationPrinter::class);
        $movedFiles = $this->removedAndAddedFilesCollector->getMovedFiles();
        foreach ($movedFiles as $movedFile) {
            $fileContent = $nodesWithFileDestinationPrinter->printNodesWithFileDestination($movedFile);
            $addedFilePathsWithContents[] = new \Rector\FileSystemRector\ValueObject\AddedFileWithContent($movedFile->getNewFilePath(), $fileContent);
        }
        $addedFilesWithNodes = $this->removedAndAddedFilesCollector->getAddedFilesWithNodes();
        if ($addedFilesWithNodes === []) {
            return $addedFilePathsWithContents;
        }
        foreach ($addedFilesWithNodes as $addedFileWithNode) {
            $fileContent = $nodesWithFileDestinationPrinter->printNodesWithFileDestination($addedFileWithNode);
            $addedFilePathsWithContents[] = new \Rector\FileSystemRector\ValueObject\AddedFileWithContent($addedFileWithNode->getFilePath(), $fileContent);
        }
        return $addedFilePathsWithContents;
    }
}
