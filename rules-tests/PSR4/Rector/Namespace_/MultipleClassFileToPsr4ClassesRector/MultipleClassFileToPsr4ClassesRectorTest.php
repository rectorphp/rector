<?php

declare(strict_types=1);

namespace Rector\Tests\PSR4\Rector\Namespace_\MultipleClassFileToPsr4ClassesRector;

use Iterator;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class MultipleClassFileToPsr4ClassesRectorTest extends AbstractRectorTestCase
{
    /**
     * @param AddedFileWithContent[] $expectedFilePathsWithContents
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $originalFileInfo, array $expectedFilePathsWithContents): void
    {
        $this->doTestFileInfo($originalFileInfo);

        $expectedFilesCount = count($expectedFilePathsWithContents);

        $movedFiles = $this->removedAndAddedFilesCollector->getMovedFiles();
        $movedFilesCount = count($movedFiles);
        $this->assertSame($expectedFilesCount, $movedFilesCount);

        $this->assertFilesWereAdded($expectedFilePathsWithContents);
    }

    /**
     * @return Iterator<mixed>
     */
    public function provideData(): Iterator
    {
        $smartFileSystem = new SmartFileSystem();

        // source: https://github.com/nette/utils/blob/798f8c1626a8e0e23116d90e588532725cce7d0e/src/Utils/exceptions.php
        $filePathsWithContents = [
            new AddedFileWithContent(
                $this->getFixtureTempDirectory() . '/RegexpException.php',
                $smartFileSystem->readFile(__DIR__ . '/Expected/RegexpException.php')
            ),
            new AddedFileWithContent(
                $this->getFixtureTempDirectory() . '/UnknownImageFileException.php',
                $smartFileSystem->readFile(__DIR__ . '/Expected/UnknownImageFileException.php')
            ),
        ];
        yield [new SmartFileInfo(__DIR__ . '/Fixture/nette_exceptions.php.inc'), $filePathsWithContents];

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

        yield [new SmartFileInfo(__DIR__ . '/Fixture/skip_without_namespace.php.inc'), $filePathsWithContents];

        $filePathsWithContents = [
            new AddedFileWithContent(
                $this->getFixtureTempDirectory() . '/MyTrait.php',
                $smartFileSystem->readFile(__DIR__ . '/Expected/MyTrait.php')
            ),
            new AddedFileWithContent(
                $this->getFixtureTempDirectory() . '/ClassTraitAndInterface.php',
                $smartFileSystem->readFile(__DIR__ . '/Expected/ClassTraitAndInterface.php')
            ),
            new AddedFileWithContent(
                $this->getFixtureTempDirectory() . '/MyInterface.php',
                $smartFileSystem->readFile(__DIR__ . '/Expected/MyInterface.php')
            ),
        ];

        yield [new SmartFileInfo(__DIR__ . '/Fixture/class_trait_and_interface.php.inc'), $filePathsWithContents];

        // keep original class
        yield [
            new SmartFileInfo(__DIR__ . '/Fixture/some_class.php.inc'),
            // extra files
            [
                new AddedFileWithContent(
                    $this->getFixtureTempDirectory() . '/SomeClass_Exception.php',
                    $smartFileSystem->readFile(__DIR__ . '/Expected/SomeClass_Exception.php')
                ),
            ],
        ];

        yield [new SmartFileInfo(__DIR__ . '/Fixture/skip_ready_exception.php.inc'), []];
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/configured_rule.php';
    }
}
