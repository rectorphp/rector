<?php

declare(strict_types=1);

namespace Rector\Core\Application;

use Rector\Core\Application\FileDecorator\FileDiffFileDecorator;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Contract\Application\ApplicationProgressBarInterface;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\FileFormatter\FileFormatter;
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
     * @var FileDiffFileDecorator
     */
    private $fileDiffFileDecorator;

    /**
     * @var FileFormatter
     */
    private $fileFormatter;

    /**
     * @var ApplicationProgressBarInterface
     */
    private $progressBar;

    /**
     * @param FileProcessorInterface[] $fileProcessors
     */
    public function __construct(
        Configuration $configuration,
        SmartFileSystem $smartFileSystem,
        FileDiffFileDecorator $fileDiffFileDecorator,
        FileFormatter $fileFormatter,
        ApplicationProgressBarInterface $progressBar,
        array $fileProcessors = []
    ) {
        $this->fileProcessors = $fileProcessors;
        $this->smartFileSystem = $smartFileSystem;
        $this->configuration = $configuration;
        $this->fileDiffFileDecorator = $fileDiffFileDecorator;
        $this->fileFormatter = $fileFormatter;
        $this->progressBar = $progressBar;
    }

    /**
     * @param File[] $files
     */
    public function run(array $files): void
    {
        $totalFileCount = count($files);

        $this->progressBar->start($totalFileCount);

        $this->processFiles($files);

        $this->progressBar->finish();

        $this->fileFormatter->format($files);

        $this->fileDiffFileDecorator->decorate($files);

        $this->printFiles($files);
    }

    /**
     * @param File[] $files
     */
    private function processFiles(array $files): void
    {
        $applicationProgressBar = $this->progressBar;

        foreach ($this->fileProcessors as $fileProcessor) {
            $supportedFiles = array_filter($files, function (File $file) use ($fileProcessor): bool {
                return $fileProcessor->supports($file);
            });

            $fileProcessor->process($supportedFiles, $applicationProgressBar);
        }
    }

    /**
     * @param File[] $files
     */
    private function printFiles(array $files): void
    {
        if ($this->configuration->isDryRun()) {
            return;
        }

        foreach ($files as $file) {
            if (! $file->hasChanged()) {
                continue;
            }

            $this->printFile($file);
        }
    }

    private function printFile(File $file): void
    {
        $smartFileInfo = $file->getSmartFileInfo();

        $this->smartFileSystem->dumpFile($smartFileInfo->getPathname(), $file->getFileContent());
        $this->smartFileSystem->chmod($smartFileInfo->getRealPath(), $smartFileInfo->getPerms());
    }
}
