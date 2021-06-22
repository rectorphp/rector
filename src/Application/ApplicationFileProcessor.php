<?php

declare(strict_types=1);

namespace Rector\Core\Application;

use Rector\Core\Application\FileDecorator\FileDiffFileDecorator;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesProcessor;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\FileFormatter\FileFormatter;
use Symplify\SmartFileSystem\SmartFileSystem;

final class ApplicationFileProcessor
{
    /**
     * @param FileProcessorInterface[] $fileProcessors
     */
    public function __construct(
        private SmartFileSystem $smartFileSystem,
        private FileDiffFileDecorator $fileDiffFileDecorator,
        private FileFormatter $fileFormatter,
        private RemovedAndAddedFilesProcessor $removedAndAddedFilesProcessor,
        private array $fileProcessors = []
    ) {
    }

    /**
     * @param File[] $files
     */
    public function run(array $files, Configuration $configuration): void
    {
        $this->processFiles($files, $configuration);
        $this->fileFormatter->format($files);

        $this->fileDiffFileDecorator->decorate($files);
        $this->printFiles($files, $configuration);
    }

    /**
     * @param File[] $files
     */
    private function processFiles(array $files, Configuration $configuration): void
    {
        foreach ($this->fileProcessors as $fileProcessor) {
            $supportedFiles = array_filter(
                $files,
                fn (File $file): bool => $fileProcessor->supports($file, $configuration)
            );

            $fileProcessor->process($supportedFiles, $configuration);
        }

        $this->removedAndAddedFilesProcessor->run($configuration);
    }

    /**
     * @param File[] $files
     */
    private function printFiles(array $files, Configuration $configuration): void
    {
        if ($configuration->isDryRun()) {
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
