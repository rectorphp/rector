<?php
declare(strict_types=1);

namespace Rector\Testing\PHPUnit\Behavior;

use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\PhpParser\Printer\NodesWithFileDestinationPrinter;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Symplify\SmartFileSystem\SmartFileInfo;
use Webmozart\Assert\Assert;

/**
 * @property-read RemovedAndAddedFilesCollector $removedAndAddedFilesCollector
 */
trait MovingFilesTrait
{
    protected function assertFileWasNotChanged(SmartFileInfo $smartFileInfo): void
    {
        $hasFileInfo = $this->removedAndAddedFilesCollector->isFileRemoved($smartFileInfo);
        $this->assertFalse($hasFileInfo);
    }

    protected function assertFileWasAdded(AddedFileWithContent $addedFileWithContent): void
    {
        $this->assertFilesWereAdded([$addedFileWithContent]);
    }

    protected function assertFileWasRemoved(SmartFileInfo $smartFileInfo): void
    {
        $isFileRemoved = $this->removedAndAddedFilesCollector->isFileRemoved($smartFileInfo);
        $this->assertTrue($isFileRemoved);
    }

    /**
     * @param AddedFileWithContent[] $expectedAddedFileWithContents
     */
    protected function assertFilesWereAdded(array $expectedAddedFileWithContents): void
    {
        Assert::allIsAOf($expectedAddedFileWithContents, AddedFileWithContent::class);
        sort($expectedAddedFileWithContents);

        $addedFilePathsWithContents = $this->resolveAddedFilePathsWithContents();
        sort($addedFilePathsWithContents);

        // there should be at least some added files
        Assert::notEmpty($addedFilePathsWithContents);

        foreach ($addedFilePathsWithContents as $key => $addedFilePathWithContent) {
            $expectedFilePathWithContent = $expectedAddedFileWithContents[$key];

            $this->assertSame(
                $expectedFilePathWithContent->getFilePath(),
                $addedFilePathWithContent->getFilePath()
            );

            $this->assertSame(
                $expectedFilePathWithContent->getFileContent(),
                $addedFilePathWithContent->getFileContent()
            );
        }
    }

    /**
     * @return AddedFileWithContent[]
     */
    private function resolveAddedFilePathsWithContents(): array
    {
        $addedFilePathsWithContents = $this->removedAndAddedFilesCollector->getAddedFilesWithContent();

        $addedFilesWithNodes = $this->removedAndAddedFilesCollector->getAddedFilesWithNodes();

        foreach ($addedFilesWithNodes as $addedFileWithNode) {
            $nodesWithFileDestinationPrinter = $this->getService(NodesWithFileDestinationPrinter::class);
            $fileContent = $nodesWithFileDestinationPrinter->printNodesWithFileDestination($addedFileWithNode);
            $addedFilePathsWithContents[] = new AddedFileWithContent($addedFileWithNode->getFilePath(), $fileContent);
        }

        return $addedFilePathsWithContents;
    }
}
