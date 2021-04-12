<?php

declare(strict_types=1);

namespace Rector\Tests\Autodiscovery\Rector\Class_\MoveEntitiesToEntityDirectoryRector;

use Iterator;
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
        $this->assertFileWasAdded($expectedAddedFileWithContent);
    }

    /**
     * @return Iterator<AddedFileWithContent[]|SmartFileInfo[]>
     */
    public function provideData(): Iterator
    {
        $smartFileSystem = new SmartFileSystem();

        yield [
            new SmartFileInfo(__DIR__ . '/Source/Controller/RandomEntity.php'),
            new AddedFileWithContent(
                $this->getFixtureTempDirectory() . '/Entity/RandomEntity.php',
                $smartFileSystem->readFile(__DIR__ . '/Expected/ExpectedRandomEntity.php')
            ),
        ];
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/configured_rule.php';
    }
}
