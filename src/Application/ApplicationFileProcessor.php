<?php

declare(strict_types=1);

namespace Rector\Core\Application;

use Rector\ChangesReporting\Application\ErrorAndDiffCollector;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use Symplify\SmartFileSystem\SmartFileSystem;

final class ApplicationFileProcessor
{
    /**
     * @var FileProcessorInterface[]
     */
    private $fileProcessors = [];

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var ErrorAndDiffCollector
     */
    private $errorAndDiffCollector;

    /**
     * @param FileProcessorInterface[] $fileProcessors
     */
    public function __construct(
        ErrorAndDiffCollector $errorAndDiffCollector,
        Configuration $configuration,
        SmartFileSystem $smartFileSystem,
        array $fileProcessors = []
    ) {
        $this->fileProcessors = $fileProcessors;
        $this->smartFileSystem = $smartFileSystem;
        $this->errorAndDiffCollector = $errorAndDiffCollector;
        $this->configuration = $configuration;
    }

    /**
     * @param File[] $files
     */
    public function run(array $files): void
    {
        $this->processFiles($files);

        foreach ($files as $file) {
            if (! $file->hasChanged()) {
                continue;
            }

            // decorate file diffs
            $this->errorAndDiffCollector->addFileDiff($file, $file->getOriginalFileContent(), $file->getFileContent());

            if ($this->configuration->isDryRun()) {
                return;
            }

            $this->printFile($file);
        }
    }

    private function printFile(File $file): void
    {
        $fileInfo = $file->getSmartFileInfo();

        $this->smartFileSystem->dumpFile($fileInfo->getPathname(), $file->getFileContent());
        $this->smartFileSystem->chmod($fileInfo->getRealPath(), $fileInfo->getPerms());
    }

    /**
     * @param File[] $files
     */
    private function processFiles(array $files): void
    {
        foreach ($files as $file) {
            foreach ($this->fileProcessors as $fileProcessor) {
                if (! $fileProcessor->supports($file)) {
                    continue;
                }

                $fileProcessor->process($file);
            }
        }
    }
}
