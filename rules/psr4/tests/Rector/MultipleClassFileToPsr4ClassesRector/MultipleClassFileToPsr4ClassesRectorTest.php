<?php

declare(strict_types=1);

namespace Rector\PSR4\Tests\Rector\MultipleClassFileToPsr4ClassesRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractFileSystemRectorTestCase;
use Rector\PSR4\Rector\MultipleClassFileToPsr4ClassesRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MultipleClassFileToPsr4ClassesRectorTest extends AbstractFileSystemRectorTestCase
{
    /**
     * @param string[] $expectedExceptions
     * @dataProvider provideData()
     */
    public function test(
        SmartFileInfo $originalFileInfo,
        array $expectedExceptions,
        bool $shouldDeleteOriginalFile
    ): void {
        $temporaryFileInfo = $this->doTestFileInfo($originalFileInfo);

        foreach ($expectedExceptions as $expectedExceptionLocation => $expectedFormat) {
            $this->assertFileExists($expectedExceptionLocation);
            $this->assertFileEquals($expectedFormat, $expectedExceptionLocation);
        }

        if ($shouldDeleteOriginalFile) {
            // PHPUnit 9.0 ready
            if (method_exists($this, 'assertFileDoesNotExist')) {
                $this->assertFileDoesNotExist($temporaryFileInfo->getRelativePathname());
            } else {
                // PHPUnit 8.0 ready
                $this->assertFileNotExists($temporaryFileInfo->getRelativePathname());
            }
        }
    }

    public function provideData(): Iterator
    {
        // source: https://github.com/nette/utils/blob/798f8c1626a8e0e23116d90e588532725cce7d0e/src/Utils/exceptions.php
        yield [
            new SmartFileInfo(__DIR__ . '/Source/nette-exceptions.php'),
            [
                $this->getFixtureTempDirectory() . '/Source/ArgumentOutOfRangeException.php' => __DIR__ . '/Expected/ArgumentOutOfRangeException.php',
                $this->getFixtureTempDirectory() . '/Source/InvalidStateException.php' => __DIR__ . '/Expected/InvalidStateException.php',
                $this->getFixtureTempDirectory() . '/Source/RegexpException.php' => __DIR__ . '/Expected/RegexpException.php',
                $this->getFixtureTempDirectory() . '/Source/UnknownImageFileException.php' => __DIR__ . '/Expected/UnknownImageFileException.php',
            ],
            true,
        ];

        yield [
            new SmartFileInfo(__DIR__ . '/Source/exceptions.php'),
            [
                $this->getFixtureTempDirectory() . '/Source/FirstException.php' => __DIR__ . '/Expected/FirstException.php',
                $this->getFixtureTempDirectory() . '/Source/SecondException.php' => __DIR__ . '/Expected/SecondException.php',
            ],
            true,
        ];

        yield [
            new SmartFileInfo(__DIR__ . '/Source/exceptions-without-namespace.php'),
            [
                $this->getFixtureTempDirectory() . '/Source/JustOneExceptionWithoutNamespace.php' => __DIR__ . '/Expected/JustOneExceptionWithoutNamespace.php',
                $this->getFixtureTempDirectory() . '/Source/JustTwoExceptionWithoutNamespace.php' => __DIR__ . '/Expected/JustTwoExceptionWithoutNamespace.php',
            ],
            true,
        ];

        yield [
            new SmartFileInfo(__DIR__ . '/Source/MissNamed.php'),
            [
                $this->getFixtureTempDirectory() . '/Source/Miss.php' => __DIR__ . '/Expected/Miss.php',
                $this->getFixtureTempDirectory() . '/Source/Named.php' => __DIR__ . '/Expected/Named.php',
            ],
            true,
        ];

        yield [
            new SmartFileInfo(__DIR__ . '/Source/ClassLike.php'),
            [
                $this->getFixtureTempDirectory() . '/Source/MyTrait.php' => __DIR__ . '/Expected/MyTrait.php',
                $this->getFixtureTempDirectory() . '/Source/MyClass.php' => __DIR__ . '/Expected/MyClass.php',
                $this->getFixtureTempDirectory() . '/Source/MyInterface.php' => __DIR__ . '/Expected/MyInterface.php',
            ],
            true,
        ];

        yield [
            new SmartFileInfo(__DIR__ . '/Source/SomeClass.php'),
            [
                $this->getFixtureTempDirectory() . '/Source/SomeClass.php' => __DIR__ . '/Expected/SomeClass.php',
                $this->getFixtureTempDirectory() . '/Source/SomeClass_Exception.php' => __DIR__ . '/Expected/SomeClass_Exception.php',
            ],
            false,
        ];
    }

    /**
     * @dataProvider provideDataForSkip()
     */
    public function testSkip(SmartFileInfo $originalFile): void
    {
        $originalContents = $originalFile->getContents();

        $this->doTestFileInfo($originalFile);

        $this->assertFileExists($originalFile->getRealPath());
        $this->assertStringEqualsFile($originalFile->getRealPath(), $originalContents);
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
