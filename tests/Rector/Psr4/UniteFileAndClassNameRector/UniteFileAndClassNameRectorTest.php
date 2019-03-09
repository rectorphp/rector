<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Psr4\UniteFileAndClassNameRector;

use Rector\FileSystemRector\FileSystemFileProcessor;
use Rector\HttpKernel\RectorKernel;
use Symfony\Component\Filesystem\Filesystem;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

/**
 * @covers \Rector\Rector\Psr4\UniteFileAndClassNameRector
 */
final class UniteFileAndClassNameRectorTest extends AbstractKernelTestCase
{
    /**
     * @var FileSystemFileProcessor
     */
    private $fileSystemFileProcessor;

    /**
     * @var Filesystem
     */
    private $filesystem;

    protected function setUp(): void
    {
        $this->bootKernelWithConfigs(RectorKernel::class, [__DIR__ . '/config.yaml']);
        $this->fileSystemFileProcessor = self::$container->get(FileSystemFileProcessor::class);

        // prepare fixture file
        $this->filesystem = self::$container->get(Filesystem::class);
        $this->filesystem->mirror(__DIR__ . '/Source/', __DIR__ . '/Fixtures/');
    }

    protected function tearDown(): void
    {
        // cleanup renamed file
        $this->filesystem->remove(__DIR__ . '/Fixtures');
    }

    public function test(): void
    {
        $this->assertFileNotExists(__DIR__ . '/Fixtures/IncorrectClassName.php');

        $file = __DIR__ . '/Fixtures/IncorrectClassNamee.php';
        $this->fileSystemFileProcessor->processFileInfo(new SmartFileInfo($file));
        $this->assertFileExists(__DIR__ . '/Fixtures/IncorrectClassName.php');

        $file = __DIR__ . '/Fixtures/IncorrectInterfaceNamee.php';
        $this->fileSystemFileProcessor->processFileInfo(new SmartFileInfo($file));
        $this->assertFileExists(__DIR__ . '/Fixtures/IncorrectInterfaceName.php');

        $file = __DIR__ . '/Fixtures/IncorrectTraitNamee.php';
        $this->fileSystemFileProcessor->processFileInfo(new SmartFileInfo($file));
        $this->assertFileExists(__DIR__ . '/Fixtures/IncorrectTraitName.php');
    }
}
