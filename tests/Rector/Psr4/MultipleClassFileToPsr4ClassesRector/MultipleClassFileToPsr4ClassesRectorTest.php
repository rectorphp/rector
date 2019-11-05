<?php

declare(strict_types=1);

namespace Rector\Tests\Rector\Psr4\MultipleClassFileToPsr4ClassesRector;

use Iterator;
use Nette\Utils\FileSystem;
use Rector\Rector\Psr4\MultipleClassFileToPsr4ClassesRector;
use Rector\Testing\PHPUnit\AbstractFileSystemRectorTestCase;

final class MultipleClassFileToPsr4ClassesRectorTest extends AbstractFileSystemRectorTestCase
{
    /**
     * @param string[] $expectedExceptions
     * @dataProvider provideDataForTest()
     */
    public function test(string $originaFile, array $expectedExceptions, bool $shouldDeleteOriginalFile): void
    {
        $temporaryFilePath = $this->doTestFile($originaFile);

        foreach ($expectedExceptions as $expectedExceptionLocation => $expectedFormat) {
            $this->assertFileExists($expectedExceptionLocation);
            $this->assertFileEquals($expectedFormat, $expectedExceptionLocation);
        }

        if ($shouldDeleteOriginalFile) {
            $this->assertFileNotExists($temporaryFilePath);
        }
    }

    public function provideDataForTest(): Iterator
    {
        // source: https://github.com/nette/utils/blob/798f8c1626a8e0e23116d90e588532725cce7d0e/src/Utils/exceptions.php
        yield 'nette_exceptions' => [
            __DIR__ . '/Source/nette-exceptions.php',
            [
                __DIR__ . '/Fixture/ArgumentOutOfRangeException.php' => __DIR__ . '/Expected/ArgumentOutOfRangeException.php',
                __DIR__ . '/Fixture/InvalidStateException.php' => __DIR__ . '/Expected/InvalidStateException.php',
                __DIR__ . '/Fixture/RegexpException.php' => __DIR__ . '/Expected/RegexpException.php',
                __DIR__ . '/Fixture/UnknownImageFileException.php' => __DIR__ . '/Expected/UnknownImageFileException.php',
            ],
            true,
        ];

        yield 'exceptions_data' => [
            __DIR__ . '/Source/exceptions.php',
            [
                __DIR__ . '/Fixture/FirstException.php' => __DIR__ . '/Expected/FirstException.php',
                __DIR__ . '/Fixture/SecondException.php' => __DIR__ . '/Expected/SecondException.php',
            ],
            true,
        ];

        yield 'non_namespaced_psr4_file_with_one_class' => [
            __DIR__ . '/Source/exceptions-without-namespace.php',
            [
                __DIR__ . '/Fixture/JustOneExceptionWithoutNamespace.php' => __DIR__ . '/Expected/JustOneExceptionWithoutNamespace.php',
                __DIR__ . '/Fixture/JustTwoExceptionWithoutNamespace.php' => __DIR__ . '/Expected/JustTwoExceptionWithoutNamespace.php',
            ],
            true,
        ];

        yield 'miss_named' => [
            __DIR__ . '/Source/MissNamed.php',
            [
                __DIR__ . '/Fixture/Miss.php' => __DIR__ . '/Expected/Miss.php',
                __DIR__ . '/Fixture/Named.php' => __DIR__ . '/Expected/Named.php',
            ],
            true,
        ];

        yield 'class_like' => [
            __DIR__ . '/Source/ClassLike.php',
            [
                __DIR__ . '/Fixture/MyTrait.php' => __DIR__ . '/Expected/MyTrait.php',
                __DIR__ . '/Fixture/MyClass.php' => __DIR__ . '/Expected/MyClass.php',
                __DIR__ . '/Fixture/MyInterface.php' => __DIR__ . '/Expected/MyInterface.php',
            ],
            true,
        ];

        yield 'provide_file_name_matching_one_class' => [
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
