<?php
declare(strict_types=1);

namespace Rector\Testing\PHPUnit\Behavior;

use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
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
        $movedFile = $this->removedAndAddedFilesCollector->getMovedFileByFileInfo($smartFileInfo);
        $this->assertNull($movedFile);
    }

    protected function assertFileWithContentWasAdded(AddedFileWithContent $addedFileWithContent): void
    {
        $this->assertFilesWereAdded([$addedFileWithContent]);
    }

    /**
     * @param AddedFileWithContent[] $expectedAddedFileWithContents
     */
    protected function assertFilesWereAdded(array $expectedAddedFileWithContents): void
    {
        Assert::allIsAOf($expectedAddedFileWithContents, AddedFileWithContent::class);

        $addedFilePathsWithContents = $this->removedAndAddedFilesCollector->getAddedFiles();

        sort($addedFilePathsWithContents);
        sort($expectedAddedFileWithContents);

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
}
