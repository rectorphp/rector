<?php

declare(strict_types=1);

namespace Rector\Core\Application;

use Rector\Core\Application\FileDecorator\FileDiffFileDecorator;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesProcessor;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\FileFormatter\FileFormatter;
use Symplify\SmartFileSystem\SmartFileSystem;

final class ApplicationFileProcessor
{
    /**
     * @param FileProcessorInterface[] $fileProcessors
     */
    public function __construct(
        private Configuration $configuration,
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
    public function run(array $files): void
    {
        $this->processFiles($files);

        $this->fileFormatter->format($files);

        $this->fileDiffFileDecorator->decorate($files);

        $this->printFiles($files);
    }

    /**
     * @param File[] $files
     */
    private function processFiles(array $files): void
    {
        foreach ($this->fileProcessors as $fileProcessor) {
            $supportedFiles = array_filter($files, fn (File $file): bool => $fileProcessor->supports($file));

            $fileProcessor->process($supportedFiles);
        }

        $this->removedAndAddedFilesProcessor->run();
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
