<?php

declare(strict_types=1);

namespace Rector\Core\Application;

use Rector\Core\Application\FileDecorator\FileDiffFileDecorator;
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
     * @var FileDiffFileDecorator
     */
    private $fileDiffFileDecorator;

    /**
     * @param FileProcessorInterface[] $fileProcessors
     */
    public function __construct(
        Configuration $configuration,
        SmartFileSystem $smartFileSystem,
        FileDiffFileDecorator $fileDiffFileDecorator,
        array $fileProcessors = []
    ) {
        $this->fileProcessors = $fileProcessors;
        $this->smartFileSystem = $smartFileSystem;
        $this->configuration = $configuration;
        $this->fileDiffFileDecorator = $fileDiffFileDecorator;
    }

    /**
     * @param File[] $files
     */
    public function run(array $files): void
    {
        $this->processFiles($files);

        $this->fileDiffFileDecorator->decorate($files);

        $this->printFiles($files);
    }

    /**
     * @param File[] $files
     */
    private function processFiles(array $files): void
    {
        foreach ($this->fileProcessors as $fileProcessor) {
            $supportedFiles = array_filter($files, function (File $file) use ($fileProcessor): bool {
                return $fileProcessor->supports($file);
            });

            $fileProcessor->process($supportedFiles);
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
