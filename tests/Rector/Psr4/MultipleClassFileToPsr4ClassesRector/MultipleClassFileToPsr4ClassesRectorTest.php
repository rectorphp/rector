<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Psr4\MultipleClassFileToPsr4ClassesRector;

use Iterator;
use Nette\Utils\FileSystem;
use Rector\Application\FileSystem\RemovedAndAddedFilesProcessor;
use Rector\Configuration\Configuration;
use Rector\FileSystemRector\FileSystemFileProcessor;
use Rector\HttpKernel\RectorKernel;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

/**
 * @covers \Rector\Rector\Psr4\MultipleClassFileToPsr4ClassesRector
 */
final class MultipleClassFileToPsr4ClassesRectorTest extends AbstractKernelTestCase
{
    /**
     * @var FileSystemFileProcessor
     */
    private $fileSystemFileProcessor;

    /**
     * @var RemovedAndAddedFilesProcessor
     */
    private $removedAndAddedFilesProcessor;

    protected function setUp(): void
    {
        $this->bootKernelWithConfigs(RectorKernel::class, [__DIR__ . '/config.yaml']);
        $this->fileSystemFileProcessor = self::$container->get(FileSystemFileProcessor::class);

        // so the files are removed and added
        $configuration = self::$container->get(Configuration::class);
        $configuration->setIsDryRun(false);

        $this->removedAndAddedFilesProcessor = self::$container->get(RemovedAndAddedFilesProcessor::class);
    }

    protected function tearDown(): void
    {
        FileSystem::delete(__DIR__ . '/Fixture');
    }

    /**
     * @param string[] $expectedExceptions
     * @dataProvider provideExceptionsData
     * @dataProvider provideMissNamed
     * @dataProvider provideClassLike
     */
    public function test(string $file, array $expectedExceptions): void
    {
        $fileInfo = new SmartFileInfo($file);

        $temporaryFilePath = $this->createTemporaryFilePath($fileInfo, $file);
        require_once $temporaryFilePath;

        $this->fileSystemFileProcessor->processFileInfo(new SmartFileInfo($temporaryFilePath));
        $this->removedAndAddedFilesProcessor->run();

        foreach ($expectedExceptions as $expectedExceptionLocation => $expectedFormat) {
            $this->assertFileExists($expectedExceptionLocation);
            $this->assertFileEquals($expectedFormat, $expectedExceptionLocation);
        }

        $this->assertFileNotExists($temporaryFilePath);
    }

    public function provideExceptionsData(): Iterator
    {
        yield [
            __DIR__ . '/Source/exceptions.php',
            [
                __DIR__ . '/Fixture/FirstException.php' => __DIR__ . '/Expected/FirstException.php',
                __DIR__ . '/Fixture/SecondException.php' => __DIR__ . '/Expected/SecondException.php',
            ],
        ];

        // source: https://github.com/nette/utils/blob/798f8c1626a8e0e23116d90e588532725cce7d0e/src/Utils/exceptions.php
        yield [
            __DIR__ . '/Source/nette-exceptions.php',
            [
                __DIR__ . '/Fixture/ArgumentOutOfRangeException.php' => __DIR__ . '/Expected/ArgumentOutOfRangeException.php',
                __DIR__ . '/Fixture/InvalidStateException.php' => __DIR__ . '/Expected/InvalidStateException.php',
                __DIR__ . '/Fixture/RegexpException.php' => __DIR__ . '/Expected/RegexpException.php',
                __DIR__ . '/Fixture/UnknownImageFileException.php' => __DIR__ . '/Expected/UnknownImageFileException.php',
            ],
        ];

        // non PSR-4 file with one class
        yield [
            __DIR__ . '/Source/exception.php',
            [
                __DIR__ . '/Fixture/JustOneException.php' => __DIR__ . '/Expected/JustOneException.php',
            ],
        ];
    }

    public function provideMissNamed(): Iterator
    {
        yield [
            __DIR__ . '/Source/MissNamed.php',
            [
                __DIR__ . '/Fixture/Miss.php' => __DIR__ . '/Expected/Miss.php',
                __DIR__ . '/Fixture/Named.php' => __DIR__ . '/Expected/Named.php',
            ],
        ];
    }

    public function provideClassLike(): Iterator
    {
        yield [
            __DIR__ . '/Source/ClassLike.php',
            [
                __DIR__ . '/Fixture/MyTrait.php' => __DIR__ . '/Expected/MyTrait.php',
                __DIR__ . '/Fixture/MyClass.php' => __DIR__ . '/Expected/MyClass.php',
                __DIR__ . '/Fixture/MyInterface.php' => __DIR__ . '/Expected/MyInterface.php',
            ],
        ];
    }

    public function testSkip(): void
    {
        $originalFileContent = (new SmartFileInfo(__DIR__ . '/Source/ReadyException.php'))->getContents();

        $this->fileSystemFileProcessor->processFileInfo(new SmartFileInfo(__DIR__ . '/Source/ReadyException.php'));
        $this->assertStringEqualsFile(__DIR__ . '/Source/ReadyException.php', $originalFileContent);
    }

    private function createTemporaryFilePath(SmartFileInfo $fileInfo, string $file): string
    {
        $temporaryFilePath = sprintf(
            '%s%sFixture%s%s',
            dirname($fileInfo->getPath()),
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            $fileInfo->getBasename()
        );

        FileSystem::copy($file, $temporaryFilePath);

        return $temporaryFilePath;
    }
}
