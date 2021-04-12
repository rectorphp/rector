<?php

declare(strict_types=1);

namespace Rector\Tests\Autodiscovery\Rector\Class_\MoveServicesBySuffixToDirectoryRector;

use Iterator;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class MoveServicesBySuffixToDirectoryRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $originalFileInfo, AddedFileWithContent $expectedAddedFileWithContent): void
    {
        $this->doTestFileInfo($originalFileInfo);
        $this->assertFileWasAdded($expectedAddedFileWithContent);
    }

    /**
     * @return Iterator<SmartFileInfo|AddedFileWithContent>
     */
    public function provideData(): Iterator
    {
        $smartFileSystem = new SmartFileSystem();

        yield [
            new SmartFileInfo(__DIR__ . '/Source/Entity/AppleRepository.php'),
            new AddedFileWithContent(
                $this->getFixtureTempDirectory() . '/Repository/AppleRepository.php',
                $smartFileSystem->readFile(__DIR__ . '/Expected/Repository/ExpectedAppleRepository.php')
            ),
        ];

        yield [
            new SmartFileInfo(__DIR__ . '/Source/Controller/BananaCommand.php'),
            new AddedFileWithContent(
                $this->getFixtureTempDirectory() . '/Command/BananaCommand.php',
                $smartFileSystem->readFile(__DIR__ . '/Expected/Command/ExpectedBananaCommand.php')
            ),
        ];

        yield [
            new SmartFileInfo(__DIR__ . '/Source/Command/MissPlacedController.php'),
            new AddedFileWithContent(
                $this->getFixtureTempDirectory() . '/Controller/MissPlacedController.php',
                $smartFileSystem->readFile(__DIR__ . '/Expected/Controller/MissPlacedController.php')
            ),
        ];

        yield [
            new SmartFileInfo(__DIR__ . '/Source/Controller/Nested/AbstractBaseWithSpaceMapper.php'),
            new AddedFileWithContent(
                $this->getFixtureTempDirectory() . '/Mapper/AbstractBaseWithSpaceMapper.php',
                $smartFileSystem->readFile(__DIR__ . '/Expected/Mapper/Nested/AbstractBaseWithSpaceMapper.php.inc')
            ),
        ];

        // inversed order, but should have the same effect
        yield [
            new SmartFileInfo(__DIR__ . '/Source/Entity/UserMapper.php'),
            new AddedFileWithContent(
                $this->getFixtureTempDirectory() . '/Mapper/UserMapper.php',
                $smartFileSystem->readFile(__DIR__ . '/Expected/Mapper/UserMapper.php.inc')
            ),
        ];
    }

    /**
     * @dataProvider provideDataSkipped()
     */
    public function testSkipped(SmartFileInfo $originalFileInfo): void
    {
        $this->doTestFileInfo($originalFileInfo);

        // no change - file should have the original location
        $this->assertFileWasNotChanged($this->originalTempFileInfo);
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public function provideDataSkipped(): Iterator
    {
        // nothing changes
        yield [new SmartFileInfo(__DIR__ . '/Source/Mapper/SkipCorrectMapper.php'), null];
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/configured_rule.php';
    }
}
