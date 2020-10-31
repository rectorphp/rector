<?php

declare(strict_types=1);

namespace Rector\PSR4\Tests\Rector\Namespace_\MultipleClassFileToPsr4ClassesRector;

use Iterator;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\ValueObject\FilePathWithContent;
use Rector\PSR4\Rector\Namespace_\MultipleClassFileToPsr4ClassesRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;
use Webmozart\Assert\Assert;

final class MultipleClassFileToPsr4ClassesRectorTest extends AbstractRectorTestCase
{
    /**
     * @param FilePathWithContent[] $expectedFilePathsWithContents
     * @dataProvider provideData()
     */
    public function test(
        SmartFileInfo $originalFileInfo,
        array $expectedFilePathsWithContents,
        bool $shouldDeleteOriginalFile
    ): void {
        $this->doTestFileInfo($originalFileInfo);
        $this->assertFilesWereAdded($expectedFilePathsWithContents);

        if ($shouldDeleteOriginalFile) {
            $this->assertFileMissing($this->getTempPath() . $originalFileInfo->getRelativePathname());
        }
    }

    public function provideData(): Iterator
    {
        $smartFileSystem = new SmartFileSystem();

        // source: https://github.com/nette/utils/blob/798f8c1626a8e0e23116d90e588532725cce7d0e/src/Utils/exceptions.php
        yield [
            new SmartFileInfo(__DIR__ . '/Source/nette-exceptions.php'),
            [
                new FilePathWithContent(
                    $this->getFixtureTempDirectory() . '/RegexpException.php',
                    $smartFileSystem->readFile(__DIR__ . '/Expected/RegexpException.php')
                ),
                new FilePathWithContent(
                    $this->getFixtureTempDirectory() . '/UnknownImageFileException.php',
                    $smartFileSystem->readFile(__DIR__ . '/Expected/UnknownImageFileException.php')
                ),
            ],
            true,
        ];

        yield [
            new SmartFileInfo(__DIR__ . '/Source/exceptions-without-namespace.php'),
            [
                new FilePathWithContent(
                    $this->getFixtureTempDirectory() . '/JustOneExceptionWithoutNamespace.php',
                    $smartFileSystem->readFile(__DIR__ . '/Expected/JustOneExceptionWithoutNamespace.php')
                ),
                new FilePathWithContent(
                    $this->getFixtureTempDirectory() . '/JustTwoExceptionWithoutNamespace.php',
                    $smartFileSystem->readFile(__DIR__ . '/Expected/JustTwoExceptionWithoutNamespace.php')
                ),
            ],
            true,
        ];

        yield [
            new SmartFileInfo(__DIR__ . '/Source/ClassTraitAndInterface.php'),
            [
                new FilePathWithContent(
                $this->getFixtureTempDirectory() . '/MyTrait.php',
                    $smartFileSystem->readFile(__DIR__ . '/Expected/MyTrait.php')
                ),
                new FilePathWithContent(
                    $this->getFixtureTempDirectory() . '/MyClass.php',
                    $smartFileSystem->readFile(__DIR__ . '/Expected/MyClass.php')
                ),
                new FilePathWithContent(
                    $this->getFixtureTempDirectory() . '/MyInterface.php',
                    $smartFileSystem->readFile(__DIR__ . '/Expected/MyInterface.php')
                ),
            ],
            true,
        ];

        // keep original class
        yield [
            new SmartFileInfo(__DIR__ . '/Source/SomeClass.php'),
            // extra files
            [
                new FilePathWithContent(
                    $this->getFixtureTempDirectory() . '/SomeClass_Exception.php',
                    $smartFileSystem->readFile(__DIR__ . '/Expected/SomeClass_Exception.php')
                ),
            ],
            false,
        ];
    }

    /**
     * @dataProvider provideDataForSkip()
     */
    public function testSkip(SmartFileInfo $originalFile): void
    {
        $originalContents = $originalFile->getContents();
        $this->doTestFileInfo($originalFile);

        $this->assertFileExists($originalFile->getRealPath());
        $this->assertStringEqualsFile($originalFile->getRealPath(), $originalContents);
    }

    public function provideDataForSkip(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureSkip');
    }

    protected function getRectorClass(): string
    {
        return MultipleClassFileToPsr4ClassesRector::class;
    }

    /**
     * @param FilePathWithContent[] $expectedFilePathsWithContents
     */
    private function assertFilesWereAdded(array $expectedFilePathsWithContents): void
    {
        Assert::allIsAOf($expectedFilePathsWithContents, FilePathWithContent::class);

        /** @var RemovedAndAddedFilesCollector $removedAndAddedFilesCollector */
        $removedAndAddedFilesCollector = self::$container->get(RemovedAndAddedFilesCollector::class);

        $addedFilePathsWithContents = $removedAndAddedFilesCollector->getAddedFilePathsWithContents();

        sort($addedFilePathsWithContents);
        sort($expectedFilePathsWithContents);

        foreach ($addedFilePathsWithContents as $key => $addedFilePathWithContent) {
            $expectedFilePathWithContent = $expectedFilePathsWithContents[$key];

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
