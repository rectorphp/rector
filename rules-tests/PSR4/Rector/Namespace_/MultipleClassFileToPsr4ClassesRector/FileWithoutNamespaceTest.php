<?php

declare(strict_types=1);

namespace Rector\Tests\PSR4\Rector\Namespace_\MultipleClassFileToPsr4ClassesRector;

use Iterator;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class FileWithoutNamespaceTest extends AbstractRectorTestCase
{
    /**
     * @param AddedFileWithContent[] $expectedFilePathsWithContents
     * @dataProvider provideData()
     */
    public function test(
        SmartFileInfo $originalFileInfo,
        array $expectedFilePathsWithContents,
        bool $expectedOriginalFileWasRemoved = true
    ): void {
        $this->doTestFileInfo($originalFileInfo);

        $this->assertCount($this->removedAndAddedFilesCollector->getAddedFileCount(), $expectedFilePathsWithContents);

        $this->assertFilesWereAdded($expectedFilePathsWithContents);

        $inputFileInfoAndExpectedFileInfo = StaticFixtureSplitter::splitFileInfoToLocalInputAndExpectedFileInfos(
            $originalFileInfo
        );

        $this->assertSame(
            $expectedOriginalFileWasRemoved,
            $this->removedAndAddedFilesCollector->isFileRemoved($inputFileInfoAndExpectedFileInfo->getInputFileInfo())
        );
    }

    /**
     * @return Iterator<mixed>
     */
    public function provideData(): Iterator
    {
        $smartFileSystem = new SmartFileSystem();

        $filePathsWithContents = [
            new AddedFileWithContent(
                $this->getFixtureTempDirectory() . '/SkipWithoutNamespace.php',
                $smartFileSystem->readFile(__DIR__ . '/Expected/SkipWithoutNamespace.php')
            ),
            new AddedFileWithContent(
                $this->getFixtureTempDirectory() . '/JustTwoExceptionWithoutNamespace.php',
                $smartFileSystem->readFile(__DIR__ . '/Expected/JustTwoExceptionWithoutNamespace.php')
            ),
        ];

        yield [
            new SmartFileInfo(__DIR__ . '/FixtureFileWithoutNamespace/some_without_namespace.php.inc'),
            $filePathsWithContents,
        ];
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/configured_rule.php';
    }
}
