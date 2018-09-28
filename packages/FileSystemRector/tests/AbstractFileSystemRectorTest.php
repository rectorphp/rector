<?php declare(strict_types=1);

namespace Rector\FileSystemRector\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Rector\DependencyInjection\ContainerFactory;
use Rector\FileSystemRector\FileSystemFileProcessor;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

abstract class AbstractFileSystemRectorTest extends TestCase
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var FileSystemFileProcessor
     */
    private $fileSystemFileProcessor;

    protected function setUp(): void
    {
        $this->container = (new ContainerFactory())->createWithConfigFiles([$this->provideConfig()]);

        $this->fileSystemFileProcessor = $this->container->get(FileSystemFileProcessor::class);
    }

    protected function doTestFile(string $file): void
    {
        $this->fileSystemFileProcessor->processFileInfo(new SmartFileInfo($file));
    }

    abstract protected function provideConfig(): string;
}
