<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileSystem\MoveValueObjectsToValueObjectDirectoryRector;

use Iterator;
use Rector\Autodiscovery\Rector\FileSystem\MoveValueObjectsToValueObjectDirectoryRector;
use Rector\Autodiscovery\Tests\Rector\FileSystem\MoveValueObjectsToValueObjectDirectoryRector\Source\ObviousValueObjectInterface;
use Rector\Core\Testing\PHPUnit\AbstractFileSystemRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MoveValueObjectsToValueObjectDirectoryRectorTest extends AbstractFileSystemRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $originalFileInfo, string $expectedFileLocation): void
    {
        $this->doTestFileInfo($originalFileInfo);
        $this->assertFileExists($expectedFileLocation);
    }

    public function provideData(): Iterator
    {
        yield [
            new SmartFileInfo(__DIR__ . '/Source/Repository/PrimitiveValueObject.php'),
            $this->getFixtureTempDirectory() . '/Source/ValueObject/PrimitiveValueObject.php',
        ];

        // type
        yield [
            new SmartFileInfo(__DIR__ . '/Source/Command/SomeName.php'),
            $this->getFixtureTempDirectory() . '/Source/ValueObject/SomeName.php',
        ];

        // suffix
        yield [
            new SmartFileInfo(__DIR__ . '/Source/Command/MeSearch.php'),
            $this->getFixtureTempDirectory() . '/Source/ValueObject/MeSearch.php',
        ];

        // skip known service types
        yield [
            new SmartFileInfo(__DIR__ . '/Source/Utils/SomeSuffixedTest.php.inc'),
            $this->getFixtureTempDirectory() . '/Source/Utils/SomeSuffixedTest.php.inc',
        ];
    }

    /**
     * @return array<string, array<string[]|bool[]>>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            MoveValueObjectsToValueObjectDirectoryRector::class => [
                MoveValueObjectsToValueObjectDirectoryRector::TYPES => [ObviousValueObjectInterface::class],
                MoveValueObjectsToValueObjectDirectoryRector::SUFFIXES => ['Search'],
                MoveValueObjectsToValueObjectDirectoryRector::ENABLE_VALUE_OBJECT_GUESSING => true,
            ],
        ];
    }

    protected function getRectorClass(): string
    {
        return MoveValueObjectsToValueObjectDirectoryRector::class;
    }
}
