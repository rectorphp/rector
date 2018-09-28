<?php declare(strict_types=1);

namespace Rector\YamlRector\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Rector\DependencyInjection\ContainerFactory;
use Rector\FileSystem\FileGuard;
use Rector\YamlRector\YamlFileProcessor;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;
use function Safe\sprintf;

abstract class AbstractYamlRectorTest extends TestCase
{
    /**
     * @var FileGuard
     */
    private $fileGuard;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var YamlFileProcessor
     */
    private $yamlFileProcessor;

    protected function setUp(): void
    {
        $config = $this->provideConfig();
        $this->fileGuard = new FileGuard();
        $this->fileGuard->ensureFileExists($config, static::class);

        $this->container = (new ContainerFactory())->createWithConfigFiles([$config]);

        $this->yamlFileProcessor = $this->container->get(YamlFileProcessor::class);
    }

    protected function doTestFileMatchesExpectedContent(string $file, string $reconstructedFile): void
    {
        $this->fileGuard->ensureFileExists($reconstructedFile, static::class);

        $reconstructedFileContent = $this->yamlFileProcessor->processFileInfo(new SmartFileInfo($file));

        $this->assertStringEqualsFile(
            $reconstructedFile,
            $reconstructedFileContent,
            sprintf('Original file "%s" did not match the result.', $file)
        );
    }

    abstract protected function provideConfig(): string;
}
