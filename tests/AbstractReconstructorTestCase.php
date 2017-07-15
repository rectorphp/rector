<?php declare(strict_types=1);

namespace Rector\Tests;

use Rector\Contract\Dispatcher\ReconstructorInterface;
use Rector\Testing\Application\FileReconstructor;
use SplFileInfo;

abstract class AbstractReconstructorTestCase extends AbstractContainerAwareTestCase
{
    /**
     * @var FileReconstructor
     */
    private $fileReconstructor;

    protected function setUp(): void
    {
        $this->fileReconstructor = $this->container->get(FileReconstructor::class);
    }

    protected function doTestFileMatchesExpectedContent(string $file, string $reconstructedFile): void
    {
        $reconstructedFileContent = $this->fileReconstructor->processFileWithReconstructor(
            new SplFileInfo($file), $this->getReconstructor()
        );

        $this->assertStringEqualsFile($reconstructedFile, $reconstructedFileContent);
    }

    abstract protected function getReconstructorClass(): string;

    private function getReconstructor(): ReconstructorInterface
    {
        return $this->container->get($this->getReconstructorClass());
    }
}
