<?php declare(strict_types=1);

namespace Rector\Testing\PHPUnit;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Rector\Contract\Rector\RectorInterface;
use Rector\DependencyInjection\ContainerFactory;
use Rector\Testing\Application\FileReconstructor;
use SplFileInfo;

abstract class AbstractReconstructorTestCase extends TestCase
{
    /**
     * @var FileReconstructor
     */
    private $fileReconstructor;

    /**
     * @var ContainerInterface
     */
    private $container;

    protected function setUp(): void
    {
        $this->container = (new ContainerFactory)->create();
        $this->fileReconstructor = $this->container->get(FileReconstructor::class);
    }

    protected function doTestFileMatchesExpectedContent(string $file, string $reconstructedFile): void
    {
        $reconstructedFileContent = $this->fileReconstructor->processFileWithRectors(
            new SplFileInfo($file),
            $this->getRectorClasses()
        );

        $this->assertStringEqualsFile($reconstructedFile, $reconstructedFileContent);
    }

    /**
     * @return string[]
     */
    abstract protected function getRectorClasses(): array;
}
