<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Application\ApplicationFileProcessor;

use Rector\Core\Application\ApplicationFileProcessor;
use Rector\Core\Configuration\Configuration;
use Rector\Core\ValueObjectFactory\Application\FileFactory;
use Rector\Core\ValueObjectFactory\ProcessResultFactory;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ApplicationFileProcessorTest extends AbstractTestCase
{
    private ApplicationFileProcessor $applicationFileProcessor;

    private FileFactory $fileFactory;

    private ProcessResultFactory $processResultFactory;

    protected function setUp(): void
    {
        $this->bootFromConfigFileInfos([new SmartFileInfo(__DIR__ . '/config/configured_rule.php')]);

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
