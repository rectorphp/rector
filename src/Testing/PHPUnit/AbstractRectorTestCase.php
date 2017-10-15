<?php declare(strict_types=1);

namespace Rector\Testing\PHPUnit;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Rector\Application\FileProcessor;
use Rector\DependencyInjection\ContainerFactory;
use Rector\Exception\FileSystem\FileNotFoundException;
use SplFileInfo;

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

    protected function setUp(): void
    {
        $this->container = (new ContainerFactory)->createWithConfig(__DIR__ . '/../../config/levels.yml');

        $this->fileProcessor = $this->container->get(FileProcessor::class);
    }

    protected function doTestFileMatchesExpectedContent(string $file, string $reconstructedFile): void
    {
        $this->ensureFileExists($file);
        $this->ensureFileExists($reconstructedFile);

        $reconstructedFileContent = $this->fileProcessor->processFileWithRectorsToString(
            new SplFileInfo($file),
            $this->getRectorClasses()
        );

        $this->assertStringEqualsFile($reconstructedFile, $reconstructedFileContent);
    }

    /**
     * @return string[]
     */
    abstract protected function getRectorClasses(): array;

    protected function ensureFileExists(string $file): void
    {
        if (! file_exists($file)) {
            throw new FileNotFoundException(sprintf(
                'File "%s" not found in "%s".',
                $file,
                get_called_class()
            ));
        }
    }
}
