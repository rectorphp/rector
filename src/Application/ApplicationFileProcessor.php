<?php

declare (strict_types=1);
namespace Rector\Core\Application;

use Rector\Core\Application\FileDecorator\FileDiffFileDecorator;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesProcessor;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Application\SystemError;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Rector\FileFormatter\FileFormatter;
use Rector\Parallel\ValueObject\Bridge;
use RectorPrefix20211221\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix20211221\Symplify\PackageBuilder\Yaml\ParametersMerger;
use RectorPrefix20211221\Symplify\SmartFileSystem\SmartFileSystem;
final class ApplicationFileProcessor
{
    /**
     * @readonly
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;
    /**
     * @readonly
     * @var \Rector\Core\Application\FileDecorator\FileDiffFileDecorator
     */
    private $fileDiffFileDecorator;
    /**
     * @readonly
     * @var \Rector\FileFormatter\FileFormatter
     */
    private $fileFormatter;
    /**
     * @readonly
     * @var \Rector\Core\Application\FileSystem\RemovedAndAddedFilesProcessor
     */
    private $removedAndAddedFilesProcessor;
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    /**
     * @readonly
     * @var \Symplify\PackageBuilder\Yaml\ParametersMerger
     */
    private $parametersMerger;
    /**
     * @var \Rector\Core\Contract\Processor\FileProcessorInterface[]
     * @readonly
     */
    private $fileProcessors = [];
    /**
     * @param FileProcessorInterface[] $fileProcessors
     */
    public function __construct(\RectorPrefix20211221\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem, \Rector\Core\Application\FileDecorator\FileDiffFileDecorator $fileDiffFileDecorator, \Rector\FileFormatter\FileFormatter $fileFormatter, \Rector\Core\Application\FileSystem\RemovedAndAddedFilesProcessor $removedAndAddedFilesProcessor, \RectorPrefix20211221\Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle, \RectorPrefix20211221\Symplify\PackageBuilder\Yaml\ParametersMerger $parametersMerger, array $fileProcessors = [])
    {
        $this->smartFileSystem = $smartFileSystem;
        $this->fileDiffFileDecorator = $fileDiffFileDecorator;
        $this->fileFormatter = $fileFormatter;
        $this->removedAndAddedFilesProcessor = $removedAndAddedFilesProcessor;
        $this->symfonyStyle = $symfonyStyle;
        $this->parametersMerger = $parametersMerger;
        $this->fileProcessors = $fileProcessors;
    }
    /**
     * @param File[] $files
     * @return array{system_errors: SystemError[], file_diffs: FileDiff[]}
     */
    public function run(array $files, \Rector\Core\ValueObject\Configuration $configuration) : array
    {
        $systemErrorsAndFileDiffs = $this->processFiles($files, $configuration);
        $this->fileFormatter->format($files);
        $this->fileDiffFileDecorator->decorate($files);
        $this->printFiles($files, $configuration);
        return $systemErrorsAndFileDiffs;
    }
    /**
     * @param File[] $files
     * @return array{system_errors: SystemError[], file_diffs: FileDiff[]}
     */
    private function processFiles(array $files, \Rector\Core\ValueObject\Configuration $configuration) : array
    {
        if ($configuration->shouldShowProgressBar()) {
            $fileCount = \count($files);
            $this->symfonyStyle->progressStart($fileCount);
        }
        $systemErrorsAndFileDiffs = [\Rector\Parallel\ValueObject\Bridge::SYSTEM_ERRORS => [], \Rector\Parallel\ValueObject\Bridge::FILE_DIFFS => []];
        foreach ($files as $file) {
            foreach ($this->fileProcessors as $fileProcessor) {
                if (!$fileProcessor->supports($file, $configuration)) {
                    continue;
                }
                $result = $fileProcessor->process($file, $configuration);
                if (\is_array($result)) {
                    $systemErrorsAndFileDiffs = $this->parametersMerger->merge($systemErrorsAndFileDiffs, $result);
                }
            }
            // progress bar +1
            if ($configuration->shouldShowProgressBar()) {
                $this->symfonyStyle->progressAdvance();
            }
        }
        $this->removedAndAddedFilesProcessor->run($configuration);
        return $systemErrorsAndFileDiffs;
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
