<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileSystem\MoveValueObjectsToValueObjectDirectoryRector;

use Iterator;
use Rector\Autodiscovery\Rector\FileSystem\MoveValueObjectsToValueObjectDirectoryRector;
use Rector\Autodiscovery\Tests\Rector\FileSystem\MoveValueObjectsToValueObjectDirectoryRector\Source\ObviousValueObjectInterface;
use Rector\Core\Testing\PHPUnit\AbstractFileSystemRectorTestCase;

final class MoveValueObjectsToValueObjectDirectoryRectorTest extends AbstractFileSystemRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $originalFile, string $expectedFileLocation): void
    {
        $this->doTestFile($originalFile);

        $this->assertFileExists($expectedFileLocation);
    }

    public function provideData(): Iterator
    {
        yield [
            __DIR__ . '/Source/Repository/PrimitiveValueObject.php',
            $this->getFixtureTempDirectory() . '/Source/ValueObject/PrimitiveValueObject.php',
        ];

        // type
        yield [
            __DIR__ . '/Source/Command/SomeName.php',
            $this->getFixtureTempDirectory() . '/Source/ValueObject/SomeName.php',
        ];

        // suffix
        yield [
            __DIR__ . '/Source/Command/MeSearch.php',
            $this->getFixtureTempDirectory() . '/Source/ValueObject/MeSearch.php',
        ];

        // skip known service types
        yield [
            __DIR__ . '/Source/Utils/SomeSuffixedTest.php.inc',
            $this->getFixtureTempDirectory() . '/Source/Utils/SomeSuffixedTest.php.inc',
        ];
    }

    protected function getRectorsWithConfiguration(): array
    {
        return [
            MoveValueObjectsToValueObjectDirectoryRector::class => [
                '$types' => [ObviousValueObjectInterface::class],
                '$suffixes' => ['Search'],
            ],
        ];
    }

    protected function getRectorClass(): string
    {
        return MoveValueObjectsToValueObjectDirectoryRector::class;
    }
}
