<?php

declare (strict_types=1);
namespace Rector\Testing\PHPUnit\Behavior;

use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\PhpParser\Printer\NodesWithFileDestinationPrinter;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Rector\Testing\Fixture\FixtureTempFileDumper;
use RectorPrefix202211\Webmozart\Assert\Assert;
/**
 * @property-read RemovedAndAddedFilesCollector $removedAndAddedFilesCollector
 */
trait MovingFilesTrait
{
    protected function assertFileWasAdded(AddedFileWithContent $addedFileWithContent) : void
    {
        $this->assertFilesWereAdded([$addedFileWithContent]);
    }
    /**
     * @param AddedFileWithContent[] $expectedAddedFileWithContents
     */
    protected function assertFilesWereAdded(array $expectedAddedFileWithContents) : void
    {
        Assert::allIsAOf($expectedAddedFileWithContents, AddedFileWithContent::class);
        \sort($expectedAddedFileWithContents);
        $addedFilePathsWithContents = $this->resolveAddedFilePathsWithContents();
        \sort($addedFilePathsWithContents);
        // there should be at least some added files
        Assert::notEmpty($addedFilePathsWithContents);
        foreach ($addedFilePathsWithContents as $key => $addedFilePathWithContent) {
            $expectedFilePathWithContent = $expectedAddedFileWithContents[$key];
            /**
             * use relative path against _temp_fixture_easy_testing
             * to make work in all OSs, for example:
             * In MacOS, the realpath() of sys_get_temp_dir() pointed to /private/var/* which symlinked of /var/*
             */
            [, $expectedFilePathWithContentFilePath] = \explode(FixtureTempFileDumper::TEMP_FIXTURE_DIRECTORY, $expectedFilePathWithContent->getFilePath());
            [, $addedFilePathWithContentFilePath] = \explode(FixtureTempFileDumper::TEMP_FIXTURE_DIRECTORY, $addedFilePathWithContent->getFilePath());
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
        foreach ($addedFilesWithNodes as $addedFileWithNode) {
            $fileContent = $nodesWithFileDestinationPrinter->printNodesWithFileDestination($addedFileWithNode);
            $addedFilePathsWithContents[] = new AddedFileWithContent($addedFileWithNode->getFilePath(), $fileContent);
        }
        return $addedFilePathsWithContents;
    }
}
