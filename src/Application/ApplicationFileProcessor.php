<?php

declare (strict_types=1);
namespace Rector\Core\Application;

use Rector\Core\Application\FileDecorator\FileDiffFileDecorator;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesProcessor;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\FileFormatter\FileFormatter;
use RectorPrefix20211020\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix20211020\Symplify\SmartFileSystem\SmartFileSystem;
final class ApplicationFileProcessor
{
    /**
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;
    /**
     * @var \Rector\Core\Application\FileDecorator\FileDiffFileDecorator
     */
    private $fileDiffFileDecorator;
    /**
     * @var \Rector\FileFormatter\FileFormatter
     */
    private $fileFormatter;
    /**
     * @var \Rector\Core\Application\FileSystem\RemovedAndAddedFilesProcessor
     */
    private $removedAndAddedFilesProcessor;
    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    /**
     * @var \Rector\Core\Contract\Processor\FileProcessorInterface[]
     */
    private $fileProcessors = [];
    /**
     * @param FileProcessorInterface[] $fileProcessors
     */
    public function __construct(\RectorPrefix20211020\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem, \Rector\Core\Application\FileDecorator\FileDiffFileDecorator $fileDiffFileDecorator, \Rector\FileFormatter\FileFormatter $fileFormatter, \Rector\Core\Application\FileSystem\RemovedAndAddedFilesProcessor $removedAndAddedFilesProcessor, \RectorPrefix20211020\Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle, array $fileProcessors = [])
    {
        $this->smartFileSystem = $smartFileSystem;
        $this->fileDiffFileDecorator = $fileDiffFileDecorator;
        $this->fileFormatter = $fileFormatter;
        $this->removedAndAddedFilesProcessor = $removedAndAddedFilesProcessor;
        $this->symfonyStyle = $symfonyStyle;
        $this->fileProcessors = $fileProcessors;
    }
    /**
     * @param File[] $files
     */
    public function run(array $files, \Rector\Core\ValueObject\Configuration $configuration) : void
    {
        $this->processFiles($files, $configuration);
        $this->fileFormatter->format($files);
        $this->fileDiffFileDecorator->decorate($files);
        $this->printFiles($files, $configuration);
    }
    /**
     * @param File[] $files
     */
    private function processFiles(array $files, \Rector\Core\ValueObject\Configuration $configuration) : void
    {
        if ($configuration->shouldShowProgressBar()) {
            $fileCount = \count($files);
            $this->symfonyStyle->progressStart($fileCount);
        }
        foreach ($files as $file) {
            foreach ($this->fileProcessors as $fileProcessor) {
                if (!$fileProcessor->supports($file, $configuration)) {
                    continue;
                }
                $fileProcessor->process($file, $configuration);
            }
            // progress bar +1
            if ($configuration->shouldShowProgressBar()) {
                $this->symfonyStyle->progressAdvance();
            }
        }
        $this->removedAndAddedFilesProcessor->run($configuration);
    }
    /**
     * @param File[] $files
     */
    private function printFiles(array $files, \Rector\Core\ValueObject\Configuration $configuration) : void
    {
        if ($configuration->isDryRun()) {
            return;
        }
        foreach ($files as $file) {
            if (!$file->hasChanged()) {
                continue;
            }
            $this->printFile($file);
        }
    }
    private function printFile(\Rector\Core\ValueObject\Application\File $file) : void
    {
        $smartFileInfo = $file->getSmartFileInfo();
        $this->smartFileSystem->dumpFile($smartFileInfo->getPathname(), $file->getFileContent());
        $this->smartFileSystem->chmod($smartFileInfo->getRealPath(), $smartFileInfo->getPerms());
    }
}
