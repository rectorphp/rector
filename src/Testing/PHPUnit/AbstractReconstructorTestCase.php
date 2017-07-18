<?php declare(strict_types=1);

namespace Rector\Testing\PHPUnit;

use PhpParser\NodeVisitor;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
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
        $reconstructedFileContent = $this->fileReconstructor->processFileWithNodeVisitor(
            new SplFileInfo($file), $this->getNodeVisitor()
        );

        $this->assertStringEqualsFile($reconstructedFile, $reconstructedFileContent);
    }

    abstract protected function getNodeVisitorClass(): string;

    private function getNodeVisitor(): NodeVisitor
    {
        return $this->container->get($this->getNodeVisitorClass());
    }
}
