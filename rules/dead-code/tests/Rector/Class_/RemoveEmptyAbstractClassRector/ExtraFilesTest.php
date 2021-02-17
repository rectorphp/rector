<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveEmptyAbstractClassRector;

use Iterator;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Testing\ValueObject\InputFilePathWithExpectedFile;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;
use Rector\DeadCode\Rector\Class_\RemoveEmptyAbstractClassRector;

final class ExtraFilesTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     * @param InputFilePathWithExpectedFile[] $extraFiles
     */
    public function test(
        SmartFileInfo $originalFileInfo,
        ?AddedFileWithContent $expectedAddedFileWithContent,
        array $extraFiles = []
    ): void {
        $this->doTestFileInfo($originalFileInfo, $extraFiles);
        $this->assertFileWithContentWasAdded($expectedAddedFileWithContent);

        $expectedAddedFilesWithContent = [];
        foreach ($extraFiles as $extraFile) {
            $expectedAddedFilesWithContent[] = $extraFile->getAddedFileWithContent();
        }

        $this->assertFilesWereAdded($expectedAddedFilesWithContent);
    }

    public function provideData(): Iterator
    {
        $smartFileSystem = new SmartFileSystem();

        $extraFiles = [
            new InputFilePathWithExpectedFile(
                __DIR__ . '/Source/UseAbstract.php',
                new AddedFileWithContent(
                    $this->getFixtureTempDirectory() . '/Source/UseAbstract.php',
                    $smartFileSystem->readFile(__DIR__ . '/Expected/UseAbstract.php')
                )
            ),
        ];

        yield [
            new SmartFileInfo(__DIR__ . '/Source/UseAbstract.php'),
            new AddedFileWithContent(
                $this->getFixtureTempDirectory() . '/Source/UseAbstract.php',
                $smartFileSystem->readFile(__DIR__ . '/Expected/UseAbstract.php')
            ),
            $extraFiles,
        ];

        $extraFiles = [
            new InputFilePathWithExpectedFile(
                __DIR__ . '/Source/ExtendsAbstractChild.php',
                new AddedFileWithContent(
                    $this->getFixtureTempDirectory() . '/Source/ExtendsAbstractChild.php',
                    $smartFileSystem->readFile(__DIR__ . '/Expected/ExtendsAbstractChild.php')
                )
            ),
        ];

        yield [
            new SmartFileInfo(__DIR__ . '/Source/ExtendsAbstractChild.php'),
            new AddedFileWithContent(
                $this->getFixtureTempDirectory() . '/Source/ExtendsAbstractChild.php',
                $smartFileSystem->readFile(__DIR__ . '/Expected/ExtendsAbstractChild.php')
            ),
            $extraFiles,
        ];
    }

    protected function getRectorClass(): string
    {
        return RemoveEmptyAbstractClassRector::class;
    }
}