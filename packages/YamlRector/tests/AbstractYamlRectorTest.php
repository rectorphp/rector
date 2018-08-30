<?php declare(strict_types=1);

namespace Rector\YamlRector\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Rector\DependencyInjection\ContainerFactory;
use Rector\FileSystem\FileGuard;
use Rector\YamlRector\YamlFileProcessor;
use Symfony\Component\Finder\SplFileInfo;

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
        $this->fileGuard->ensureFileExists($config, get_called_class());

        $this->container = (new ContainerFactory())->createWithConfigFiles([$config]);

        $this->yamlFileProcessor = $this->container->get(YamlFileProcessor::class);
    }

    protected function doTestFileMatchesExpectedContent(string $file, string $reconstructedFile): void
    {
        $this->fileGuard->ensureFileExists($file, get_called_class());
        $this->fileGuard->ensureFileExists($reconstructedFile, get_called_class());

        $reconstructedFileContent = $this->yamlFileProcessor->processFileInfo(new SplFileInfo($file, '', ''));

        $this->assertStringEqualsFile($reconstructedFile, $reconstructedFileContent, sprintf(
            'Original file "%s" did not match the result.',
            $file
        ));
    }

    abstract protected function provideConfig(): string;
}
