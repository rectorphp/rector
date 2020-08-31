<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileSystem\MoveServicesBySuffixToDirectoryRector;

use Iterator;
use Rector\Autodiscovery\Rector\FileSystem\MoveServicesBySuffixToDirectoryRector;
use Rector\Core\Testing\PHPUnit\AbstractFileSystemRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MoveServicesBySuffixToDirectoryRectorTest extends AbstractFileSystemRectorTestCase
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
            new SmartFileInfo(__DIR__ . '/Source/Entity/AppleRepository.php'),
            $this->getFixtureTempDirectory() . '/Source/Repository/AppleRepository.php',
            __DIR__ . '/Expected/Repository/ExpectedAppleRepository.php',
        ];

        yield [
            new SmartFileInfo(__DIR__ . '/Source/Controller/BananaCommand.php'),
            $this->getFixtureTempDirectory() . '/Source/Command/BananaCommand.php',
            __DIR__ . '/Expected/Command/ExpectedBananaCommand.php',
        ];

        yield [
            new SmartFileInfo(__DIR__ . '/Source/Mapper/CorrectMapper.php'),
            $this->getFixtureTempDirectory() . '/Source/Mapper/CorrectMapper.php',
            // same content, no change
            __DIR__ . '/Source/Mapper/CorrectMapper.php',
        ];

        yield [
            new SmartFileInfo(__DIR__ . '/Source/Command/MissPlacedController.php'),
            $this->getFixtureTempDirectory() . '/Source/Controller/MissPlacedController.php',
            __DIR__ . '/Expected/Controller/MissPlacedController.php',
        ];

        // skip interface
        yield [
            new SmartFileInfo(__DIR__ . '/Source/Command/MightBeController.php'),
            $this->getFixtureTempDirectory() . '/Source/Command/MightBeController.php',
            // same content, no change
            __DIR__ . '/Source/Command/MightBeController.php',
        ];
    }

    /**
     * @return array<string, array<string[]>>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            MoveServicesBySuffixToDirectoryRector::class => [
                MoveServicesBySuffixToDirectoryRector::GROUP_NAMES_BY_SUFFIX => [
                    'Repository',
                    'Command',
                    'Mapper',
                    'Controller',
                ],
            ],
        ];
    }
}
