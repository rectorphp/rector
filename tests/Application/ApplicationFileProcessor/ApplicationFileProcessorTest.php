<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Application\ApplicationFileProcessor;

use Rector\ChangesReporting\Application\ErrorAndDiffCollector;
use Rector\Core\Application\ApplicationFileProcessor;
use Rector\Core\Application\FileFactory;
use Rector\Core\Configuration\Configuration;
use Rector\Core\HttpKernel\RectorKernel;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class ApplicationFileProcessorTest extends AbstractKernelTestCase
{
    /**
     * @var ApplicationFileProcessor
     */
    private $applicationFileProcessor;

    /**
     * @var ErrorAndDiffCollector
     */
    private $errorAndDiffCollector;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    protected function setUp(): void
    {
        $this->bootKernelWithConfigs(RectorKernel::class, [__DIR__ . '/config/configured_rule.php']);

        /** @var Configuration $configuration */
        $configuration = $this->getService(Configuration::class);
        $configuration->setIsDryRun(true);

        $this->applicationFileProcessor = $this->getService(ApplicationFileProcessor::class);
        $this->errorAndDiffCollector = $this->getService(ErrorAndDiffCollector::class);
        $this->fileFactory = $this->getService(FileFactory::class);
    }

    public function test(): void
    {
        $files = $this->fileFactory->createFromPaths([__DIR__ . '/Fixture']);
        $this->assertCount(2, $files);

        $this->applicationFileProcessor->run($files);

        $fileDiffs = [];
        foreach ($files as $file) {
            if ($file->getFileDiff() === null) {
                continue;
            }

            $fileDiffs[] = $fileDiffs;
        }

        $this->assertCount(1, $fileDiffs);
    }
}
