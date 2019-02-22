<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Psr4\MultipleClassFileToPsr4ClassesRector;

use Iterator;
use Rector\FileSystemRector\FileSystemFileProcessor;
use Rector\HttpKernel\RectorKernel;
use Symfony\Component\Filesystem\Filesystem;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class MultipleClassFileToPsr4ClassesRectorTest extends AbstractKernelTestCase
{
    /**
     * @var FileSystemFileProcessor
     */
    private $fileSystemFileProcessor;

    protected function setUp(): void
    {
        $this->bootKernelWithConfigs(RectorKernel::class, [__DIR__ . '/config.yaml']);
        $this->fileSystemFileProcessor = self::$container->get(FileSystemFileProcessor::class);
    }

    protected function tearDown(): void
    {
        if (! $this->getProvidedData()) {
            return;
        }

        // cleanup filesystem
        $generatedFiles = array_keys($this->getProvidedData()[1]);
        (new Filesystem())->remove($generatedFiles);
    }

    /**
     * @param string[] $expectedExceptions
     * @dataProvider provideExceptionsData
     */
    public function test(string $file, array $expectedExceptions): void
    {
        $this->fileSystemFileProcessor->processFileInfo(new SmartFileInfo($file));

        foreach ($expectedExceptions as $expectedExceptionLocation => $expectedFormat) {
            $this->assertFileExists($expectedExceptionLocation);
            $this->assertFileEquals($expectedFormat, $expectedExceptionLocation);
        }
    }

    public function provideExceptionsData(): Iterator
    {
        yield [
            __DIR__ . '/Source/exceptions.php',
            [
                __DIR__ . '/Source/FirstException.php' => __DIR__ . '/Expected/FirstException.php',
                __DIR__ . '/Source/SecondException.php' => __DIR__ . '/Expected/SecondException.php',
            ],
        ];

        // source: https://github.com/nette/utils/blob/798f8c1626a8e0e23116d90e588532725cce7d0e/src/Utils/exceptions.php
        yield [
            __DIR__ . '/Source/nette-exceptions.php',
            [
                __DIR__ . '/Source/ArgumentOutOfRangeException.php' => __DIR__ . '/Expected/ArgumentOutOfRangeException.php',
                __DIR__ . '/Source/InvalidStateException.php' => __DIR__ . '/Expected/InvalidStateException.php',
                __DIR__ . '/Source/RegexpException.php' => __DIR__ . '/Expected/RegexpException.php',
                __DIR__ . '/Source/UnknownImageFileException.php' => __DIR__ . '/Expected/UnknownImageFileException.php',
            ],
        ];

        // non PSR-4 file with one class
        yield [
            __DIR__ . '/Source/exception.php',
            [
                __DIR__ . '/Source/JustOneException.php' => __DIR__ . '/Expected/JustOneException.php',
            ],
        ];
    }

    public function testSkip(): void
    {
        $originalFileContent = (new SmartFileInfo(__DIR__ . '/Source/ReadyException.php'))->getContents();

        $this->fileSystemFileProcessor->processFileInfo(new SmartFileInfo(__DIR__ . '/Source/ReadyException.php'));
        $this->assertStringEqualsFile(__DIR__ . '/Source/ReadyException.php', $originalFileContent);
    }
}
