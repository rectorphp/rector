<?php declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileSystem\MoveServicesBySuffixToDirectoryRector;

use Iterator;
use Nette\Utils\FileSystem;
use Rector\Autodiscovery\Rector\FileSystem\MoveServicesBySuffixToDirectoryRector;
use Rector\Testing\PHPUnit\AbstractFileSystemRectorTestCase;

final class MoveServicesBySuffixToDirectoryRectorTest extends AbstractFileSystemRectorTestCase
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
            __DIR__ . '/Source/Entity/AppleRepository.php',
            __DIR__ . '/Source/Fixture/Repository/AppleRepository.php',
            __DIR__ . '/Fixture/ExpectedAppleRepository.php',
        ];

        yield 'prefix_same_namespace' => [
            __DIR__ . '/Source/Controller/BananaCommand.php',
            __DIR__ . '/Source/Fixture/Command/BananaCommand.php',
            __DIR__ . '/Fixture/Command/ExpectedBananaCommand.php',
        ];
    }

    protected function getRectorsWithConfiguration(): array
    {
        return [
            MoveServicesBySuffixToDirectoryRector::class => [
                '$groupNamesBySuffix' => ['Repository', 'Command'],
            ],
        ];
    }
}
