<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Application\ApplicationFileProcessor;

use Rector\Core\Application\ApplicationFileProcessor;
use Rector\Core\Application\FileFactory;
use Rector\Core\Configuration\Configuration;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\ValueObjectFactory\ProcessResultFactory;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class ApplicationFileProcessorTest extends AbstractKernelTestCase
{
    /**
     * @var ApplicationFileProcessor
     */
    private $applicationFileProcessor;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var ProcessResultFactory
     */
    private $processResultFactory;

    protected function setUp(): void
    {
        $this->bootKernelWithConfigs(RectorKernel::class, [__DIR__ . '/config/configured_rule.php']);

        /** @var Configuration $configuration */
        $configuration = $this->getService(Configuration::class);
        $configuration->setIsDryRun(true);

        $this->applicationFileProcessor = $this->getService(ApplicationFileProcessor::class);
        $this->fileFactory = $this->getService(FileFactory::class);
        $this->processResultFactory = $this->getService(ProcessResultFactory::class);
    }

    public function test(): void
    {
        $files = $this->fileFactory->createFromPaths([__DIR__ . '/Fixture']);
        $this->assertCount(2, $files);

        $this->applicationFileProcessor->run($files);

        $processResult = $this->processResultFactory->create($files);
        $this->assertCount(1, $processResult->getFileDiffs());
    }
}
