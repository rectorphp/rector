<?php declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileSystem\MoveEntitiesToEntityDirectoryRector;

use Iterator;
use Nette\Utils\FileSystem;
use Rector\Autodiscovery\Rector\FileSystem\MoveEntitiesToEntityDirectoryRector;
use Rector\Testing\PHPUnit\AbstractFileSystemRectorTestCase;

final class MoveEntitiesToEntityDirectoryRectorTest extends AbstractFileSystemRectorTestCase
{
    protected function tearDown(): void
    {
        FileSystem::delete(__DIR__ . '/Source/Fixture');
    }

    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $originalFile, string $expectedFileLocation, string $expectedFileContent): void
    {
        $this->doTestFile($originalFile);

        $this->assertFileExists($expectedFileLocation);
        $this->assertFileEquals($expectedFileContent, $expectedFileLocation);
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): Iterator
    {
        yield [
            __DIR__ . '/Source/Controller/RandomEntity.php',
            __DIR__ . '/Source/Fixture/Entity/RandomEntity.php',
            __DIR__ . '/Fixture/ExpectedRandomEntity.php',
        ];
    }

    protected function getRectorClass(): string
    {
        return MoveEntitiesToEntityDirectoryRector::class;
    }
}
