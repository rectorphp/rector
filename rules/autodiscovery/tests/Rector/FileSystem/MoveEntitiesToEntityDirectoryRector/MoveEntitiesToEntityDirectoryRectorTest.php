<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileSystem\MoveEntitiesToEntityDirectoryRector;

use Iterator;
use Rector\Autodiscovery\Rector\FileSystem\MoveEntitiesToEntityDirectoryRector;
use Rector\Core\Testing\PHPUnit\AbstractFileSystemRectorTestCase;

final class MoveEntitiesToEntityDirectoryRectorTest extends AbstractFileSystemRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $originalFile, string $expectedFileLocation): void
    {
        $this->doTestFile($originalFile);

        $this->assertFileExists($expectedFileLocation);
        $this->assertFileEquals($expectedFileLocation, $expectedFileLocation);
    }

    public function provideData(): Iterator
    {
        yield [
            __DIR__ . '/Source/Controller/RandomEntity.php',
            __DIR__ . '/Source/Entity/Fixture/RandomEntity.php',
        ];
    }

    protected function getRectorClass(): string
    {
        return MoveEntitiesToEntityDirectoryRector::class;
    }
}
