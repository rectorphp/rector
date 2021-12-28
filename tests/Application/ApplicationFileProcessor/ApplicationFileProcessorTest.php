<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Application\ApplicationFileProcessor;

use Rector\Core\Application\ApplicationFileProcessor;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObjectFactory\Application\FileFactory;
use Rector\Core\ValueObjectFactory\ProcessResultFactory;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Symfony\Component\Console\Input\ArrayInput;

final class ApplicationFileProcessorTest extends AbstractTestCase
{
    private ApplicationFileProcessor $applicationFileProcessor;

    private FileFactory $fileFactory;

    private ProcessResultFactory $processResultFactory;

    protected function setUp(): void
    {
        $this->bootFromConfigFiles([__DIR__ . '/config/configured_rule.php']);

        $this->applicationFileProcessor = $this->getService(ApplicationFileProcessor::class);
        $this->fileFactory = $this->getService(FileFactory::class);
        $this->processResultFactory = $this->getService(ProcessResultFactory::class);
    }

    public function test(): void
    {
        $paths = [__DIR__ . '/Fixture'];

        $configuration = new Configuration(isDryRun: true, paths: $paths);

        $files = $this->fileFactory->createFromPaths($paths, $configuration);
        $this->assertCount(2, $files);

        $systemErrorsAndFileDiffs = $this->applicationFileProcessor->run($configuration, new ArrayInput([]));

        $processResult = $this->processResultFactory->create($systemErrorsAndFileDiffs);

        $this->assertCount(1, $processResult->getFileDiffs());
    }
}
