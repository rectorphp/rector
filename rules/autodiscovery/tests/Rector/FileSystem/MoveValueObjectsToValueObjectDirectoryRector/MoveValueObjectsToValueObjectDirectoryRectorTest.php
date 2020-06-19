<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileSystem\MoveValueObjectsToValueObjectDirectoryRector;

use Iterator;
use Rector\Autodiscovery\Rector\FileSystem\MoveValueObjectsToValueObjectDirectoryRector;
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
    }

    protected function getRectorClass(): string
    {
        return MoveValueObjectsToValueObjectDirectoryRector::class;
    }
}
