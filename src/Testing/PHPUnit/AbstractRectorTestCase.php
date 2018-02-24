<?php declare(strict_types=1);

namespace Rector\Testing\PHPUnit;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Rector\Application\FileProcessor;
use Rector\DependencyInjection\ContainerFactory;
use Rector\FileSystem\FileGuard;
use SplFileInfo;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

abstract class AbstractRectorTestCase extends TestCase
{
    /**
     * @var FileProcessor
     */
    protected $fileProcessor;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ParameterProvider
     */
    protected $parameterProvider;

    protected function setUp(): void
    {
        $this->container = (new ContainerFactory())->createWithConfig($this->provideConfig());
        $this->fileProcessor = $this->container->get(FileProcessor::class);
        $this->parameterProvider = $this->container->get(ParameterProvider::class);
    }

    protected function doTestFileMatchesExpectedContent(string $file, string $reconstructedFile): void
    {
        FileGuard::ensureFileExists($file, get_called_class());
        FileGuard::ensureFileExists($reconstructedFile, get_called_class());

        $this->parameterProvider->changeParameter('source', [$file]);

        $reconstructedFileContent = $this->fileProcessor->processFileToString(new SplFileInfo($file));

        $this->assertStringEqualsFile($reconstructedFile, $reconstructedFileContent, sprintf(
            'Original file "%s" did not match the result.',
            $file
        ));
    }

    abstract protected function provideConfig(): string;
}
