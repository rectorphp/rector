<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileSystem\MoveEntitiesToEntityDirectoryRector;

use Iterator;
use Rector\Autodiscovery\Rector\FileSystem\MoveEntitiesToEntityDirectoryRector;
use Rector\Core\Testing\PHPUnit\AbstractFileSystemRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MoveEntitiesToEntityDirectoryRectorTest extends AbstractFileSystemRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(
        SmartFileInfo $originalFileInfo,
        string $expectedFileLocation,
        string $expectedFileContent
    ): void {
        $this->doTestFileInfo($originalFileInfo);

        $this->assertFileExists($expectedFileLocation);
        $this->assertFileEquals($expectedFileContent, $expectedFileLocation);
    }

    public function provideData(): Iterator
    {
        yield [
            new SmartFileInfo(__DIR__ . '/Source/Controller/RandomEntity.php'),
            $this->getFixtureTempDirectory() . '/Source/Entity/RandomEntity.php',
            __DIR__ . '/Expected/ExpectedRandomEntity.php',
        ];
    }

    protected function getRectorClass(): string
    {
        return MoveEntitiesToEntityDirectoryRector::class;
    }
}
