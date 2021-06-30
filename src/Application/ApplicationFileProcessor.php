<?php

declare (strict_types=1);
namespace Rector\Core\Application;

use Rector\Core\Application\FileDecorator\FileDiffFileDecorator;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesProcessor;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\FileFormatter\FileFormatter;
use RectorPrefix20210630\Symplify\SmartFileSystem\SmartFileSystem;
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
     * @var mixed[]
     */
    private $fileProcessors = [];
    /**
     * @param FileProcessorInterface[] $fileProcessors
     */
    public function __construct(\RectorPrefix20210630\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem, \Rector\Core\Application\FileDecorator\FileDiffFileDecorator $fileDiffFileDecorator, \Rector\FileFormatter\FileFormatter $fileFormatter, \Rector\Core\Application\FileSystem\RemovedAndAddedFilesProcessor $removedAndAddedFilesProcessor, array $fileProcessors = [])
    {
        $this->smartFileSystem = $smartFileSystem;
        $this->fileDiffFileDecorator = $fileDiffFileDecorator;
        $this->fileFormatter = $fileFormatter;
        $this->removedAndAddedFilesProcessor = $removedAndAddedFilesProcessor;
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
        foreach ($this->fileProcessors as $fileProcessor) {
            $supportedFiles = \array_filter($files, function (\Rector\Core\ValueObject\Application\File $file) use($fileProcessor, $configuration) : bool {
                return $fileProcessor->supports($file, $configuration);
            });
            $fileProcessor->process($supportedFiles, $configuration);
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
