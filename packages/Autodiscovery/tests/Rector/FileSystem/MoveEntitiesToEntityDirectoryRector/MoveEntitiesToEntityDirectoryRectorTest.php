<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileSystem\MoveEntitiesToEntityDirectoryRector;

use Iterator;
use Rector\Autodiscovery\Rector\FileSystem\MoveEntitiesToEntityDirectoryRector;
use Rector\Testing\PHPUnit\AbstractFileSystemRectorTestCase;

final class MoveEntitiesToEntityDirectoryRectorTest extends AbstractFileSystemRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $originalFile, string $expectedFileLocation, string $expectedFileContent): void
    {
        $this->doTestFile($originalFile);

        $this->assertFileExists($expectedFileLocation);
        $this->assertFileEquals($expectedFileContent, $expectedFileLocation);
    }

    public function provideDataForTest(): Iterator
    {
        yield [
            __DIR__ . '/Source/Controller/RandomEntity.php',
            __DIR__ . '/Source/Fixture/Entity/RandomEntity.php',
            __DIR__ . '/Expected/ExpectedRandomEntity.php',
        ];
    }

    protected function getRectorClass(): string
    {
        return MoveEntitiesToEntityDirectoryRector::class;
    }
}
