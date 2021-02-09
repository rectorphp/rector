<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileNode\MoveEntitiesToEntityDirectoryRector;

use Iterator;
use Rector\Autodiscovery\Rector\FileNode\MoveEntitiesToEntityDirectoryRector;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class MoveEntitiesToEntityDirectoryRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(
        SmartFileInfo $originalFileInfo,
        AddedFileWithContent $expectedAddedFileWithContent
    ): void {
        $this->doTestFileInfo($originalFileInfo);
        $this->assertFileWithContentWasAdded($expectedAddedFileWithContent);
    }

    public function provideData(): Iterator
    {
        $smartFileSystem = new SmartFileSystem();

        yield [
            new SmartFileInfo(__DIR__ . '/Source/Controller/RandomEntity.php'),
            new AddedFileWithContent(
                $this->getFixtureTempDirectory() . '/Source/Entity/RandomEntity.php',
                $smartFileSystem->readFile(__DIR__ . '/Expected/ExpectedRandomEntity.php')
            ),
        ];
    }

    protected function getRectorClass(): string
    {
        return MoveEntitiesToEntityDirectoryRector::class;
    }
}
