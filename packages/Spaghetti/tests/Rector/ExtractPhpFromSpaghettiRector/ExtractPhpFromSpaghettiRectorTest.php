<?php declare(strict_types=1);

namespace Rector\Spaghetti\Tests\Rector\ExtractPhpFromSpaghettiRector;

use Iterator;
use Nette\Utils\FileSystem;
use Rector\FileSystemRector\FileSystemFileProcessor;
use Rector\HttpKernel\RectorKernel;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

/**
 * @covers \Rector\Spaghetti\Rector\ExtractPhpFromSpaghettiRector
 */
final class ExtractPhpFromSpaghettiRectorTest extends AbstractKernelTestCase
{
    /**
     * @var FileSystemFileProcessor
     */
    private $fileSystemFileProcessor;

    protected function setUp(): void
    {
        $this->bootKernelWithConfigs(RectorKernel::class, [__DIR__ . '/config.yaml']);

        $this->fileSystemFileProcessor = self::$container->get(FileSystemFileProcessor::class);

        FileSystem::copy(__DIR__ . '/Backup', __DIR__ . '/Source');
    }

    protected function tearDown(): void
    {
        if (! $this->getProvidedData()) {
            return;
        }

        // cleanup filesystem
        FileSystem::delete(__DIR__ . '/Source');
    }

    /**
     * @dataProvider provideExceptionsData
     */
    public function test(string $originalFile, string $expectedFile): void
    {
        $this->fileSystemFileProcessor->processFileInfo(new SmartFileInfo($originalFile));

        $this->assertFileEquals($expectedFile, $originalFile);
    }

    public function provideExceptionsData(): Iterator
    {
        yield [__DIR__ . '/Source/index.php', __DIR__ . '/Expected/index.php'];
        yield [__DIR__ . '/Source/simple_foreach.php', __DIR__ . '/Expected/simple_foreach.php'];
    }
}
