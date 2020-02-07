<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileSystem\MoveServicesBySuffixToDirectoryRector;

use Iterator;
use Rector\Autodiscovery\Rector\FileSystem\MoveServicesBySuffixToDirectoryRector;
use Rector\Core\Testing\PHPUnit\AbstractFileSystemRectorTestCase;

final class MoveServicesBySuffixToDirectoryRectorTest extends AbstractFileSystemRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $originalFile, string $expectedFileLocation, string $expectedFileContent): void
    {
        $this->doTestFile($originalFile);

        $this->assertFileExists($expectedFileLocation);
        $this->assertFileEquals($expectedFileContent, $expectedFileLocation);
    }

    public function provideData(): Iterator
    {
        yield [
            __DIR__ . '/Source/Entity/AppleRepository.php',
            __DIR__ . '/Source/Fixture/Repository/AppleRepository.php',
            __DIR__ . '/Expected/Repository/ExpectedAppleRepository.php',
        ];

        yield 'prefix_same_namespace' => [
            __DIR__ . '/Source/Controller/BananaCommand.php',
            __DIR__ . '/Source/Command/Fixture/BananaCommand.php',
            __DIR__ . '/Expected/Command/ExpectedBananaCommand.php',
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
