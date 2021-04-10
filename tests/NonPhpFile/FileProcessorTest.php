<?php
declare(strict_types=1);

namespace Rector\Core\Tests\NonPhpFile;

use Rector\ChangesReporting\Application\ErrorAndDiffCollector;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Configuration\Option;
use Rector\Core\NonPhpFile\FileProcessor;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FileProcessorTest extends AbstractRectorTestCase
{
    /**
     * @var FileProcessor
     */
    private $nonPhpFileProcessorService;

    /**
     * @var ErrorAndDiffCollector
     */
    private $errorAndDiffCollector;

    protected function setUp(): void
    {
        parent::setUp();
        /** @var Configuration $configuration */
        $configuration = $this->getService(Configuration::class);
        $configuration->setIsDryRun(true);

        $this->nonPhpFileProcessorService = $this->getService(FileProcessor::class);
        $this->errorAndDiffCollector = $this->getService(ErrorAndDiffCollector::class);
    }

    public function test(): void
    {
        $paths = $this->parameterProvider->provideParameter(Option::PATHS);
        $this->nonPhpFileProcessorService->runOnPaths($paths);

        $fileDiffs = $this->errorAndDiffCollector->getFileDiffs();
        $this->assertCount(1, $fileDiffs);
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/configured_rule.php';
    }
}
