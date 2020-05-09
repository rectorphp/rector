<?php

declare(strict_types=1);

namespace Rector\PSR4\Tests\Rector\MultipleClassFileToPsr4ClassesRector;

use Iterator;
use Nette\Utils\FileSystem;
use Rector\Core\Testing\PHPUnit\AbstractFileSystemRectorTestCase;
use Rector\PSR4\Rector\MultipleClassFileToPsr4ClassesRector;

final class MultipleClassFileToPsr4ClassesRectorTest extends AbstractFileSystemRectorTestCase
{
    /**
     * @param string[] $expectedExceptions
     * @dataProvider provideData()
     */
    public function test(string $originalFile, array $expectedExceptions, bool $shouldDeleteOriginalFile): void
    {
        $this->assertFileExists($originalFile);

        $temporaryFilePath = $this->doTestFile($originalFile);

        foreach ($expectedExceptions as $expectedExceptionLocation => $expectedFormat) {
            $this->assertFileExists($expectedExceptionLocation);
            $this->assertFileEquals($expectedFormat, $expectedExceptionLocation);
        }

        if ($shouldDeleteOriginalFile) {
            // PHPUnit 9.0 ready
            if (method_exists($this, 'assertFileDoesNotExist')) {
                $this->assertFileDoesNotExist($temporaryFilePath);
            } else {
                // PHPUnit 8.0 ready
                $this->assertFileNotExists($temporaryFilePath);
            }
        }
    }

    public function provideData(): Iterator
    {
        // source: https://github.com/nette/utils/blob/798f8c1626a8e0e23116d90e588532725cce7d0e/src/Utils/exceptions.php
        yield [
            __DIR__ . '/Source/nette-exceptions.php',
            [
                __DIR__ . '/Fixture/ArgumentOutOfRangeException.php' => __DIR__ . '/Expected/ArgumentOutOfRangeException.php',
                __DIR__ . '/Fixture/InvalidStateException.php' => __DIR__ . '/Expected/InvalidStateException.php',
                __DIR__ . '/Fixture/RegexpException.php' => __DIR__ . '/Expected/RegexpException.php',
                __DIR__ . '/Fixture/UnknownImageFileException.php' => __DIR__ . '/Expected/UnknownImageFileException.php',
            ],
            true,
        ];

        yield [
            __DIR__ . '/Source/exceptions.php',
            [
                __DIR__ . '/Fixture/FirstException.php' => __DIR__ . '/Expected/FirstException.php',
                __DIR__ . '/Fixture/SecondException.php' => __DIR__ . '/Expected/SecondException.php',
            ],
            true,
        ];

        yield [
            __DIR__ . '/Source/exceptions-without-namespace.php',
            [
                __DIR__ . '/Fixture/JustOneExceptionWithoutNamespace.php' => __DIR__ . '/Expected/JustOneExceptionWithoutNamespace.php',
                __DIR__ . '/Fixture/JustTwoExceptionWithoutNamespace.php' => __DIR__ . '/Expected/JustTwoExceptionWithoutNamespace.php',
            ],
            true,
        ];

        yield [
            __DIR__ . '/Source/MissNamed.php',
            [
                __DIR__ . '/Fixture/Miss.php' => __DIR__ . '/Expected/Miss.php',
                __DIR__ . '/Fixture/Named.php' => __DIR__ . '/Expected/Named.php',
            ],
            true,
        ];

        yield [
            __DIR__ . '/Source/ClassLike.php',
            [
                __DIR__ . '/Fixture/MyTrait.php' => __DIR__ . '/Expected/MyTrait.php',
                __DIR__ . '/Fixture/MyClass.php' => __DIR__ . '/Expected/MyClass.php',
                __DIR__ . '/Fixture/MyInterface.php' => __DIR__ . '/Expected/MyInterface.php',
            ],
            true,
        ];

        yield [
            __DIR__ . '/Source/SomeClass.php',
            [
                __DIR__ . '/Fixture/SomeClass.php' => __DIR__ . '/Expected/SomeClass.php',
                __DIR__ . '/Fixture/SomeClass_Exception.php' => __DIR__ . '/Expected/SomeClass_Exception.php',
            ],
            false,
        ];
    }

    /**
     * @dataProvider provideDataForSkip()
     */
    public function testSkip(string $originalFile): void
    {
        $originalFileContent = FileSystem::read($originalFile);

        $this->doTestFile($originalFile);

        $this->assertFileExists($originalFile);
        $this->assertStringEqualsFile($originalFile, $originalFileContent);
    }

    public function provideDataForSkip(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureSkip');
    }

    protected function getRectorClass(): string
    {
        return MultipleClassFileToPsr4ClassesRector::class;
    }
}
