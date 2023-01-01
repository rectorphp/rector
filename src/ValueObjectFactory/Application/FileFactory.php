<?php

declare (strict_types=1);
namespace Rector\Core\ValueObjectFactory\Application;

use RectorPrefix202301\Nette\Utils\FileSystem;
use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\FileSystem\FilesFinder;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
/**
 * @see \Rector\Core\ValueObject\Application\File
 */
final class FileFactory
{
    /**
     * @readonly
     * @var \Rector\Core\FileSystem\FilesFinder
     */
    private $filesFinder;
    /**
     * @readonly
     * @var \Rector\Caching\Detector\ChangedFilesDetector
     */
    private $changedFilesDetector;
    /**
     * @var FileProcessorInterface[]
     * @readonly
     */
    private $fileProcessors;
    /**
     * @param FileProcessorInterface[] $fileProcessors
     */
    public function __construct(FilesFinder $filesFinder, ChangedFilesDetector $changedFilesDetector, array $fileProcessors)
    {
        $this->filesFinder = $filesFinder;
        $this->changedFilesDetector = $changedFilesDetector;
        $this->fileProcessors = $fileProcessors;
    }
    /**
     * @param string[] $paths
     * @return string[]
     */
    public function createFileInfosFromPaths(array $paths, Configuration $configuration) : array
    {
        if ($configuration->shouldClearCache()) {
            $this->changedFilesDetector->clear();
        }
        $supportedFileExtensions = $this->resolveSupportedFileExtensions($configuration);
        return $this->filesFinder->findInDirectoriesAndFiles($paths, $supportedFileExtensions);
    }
    /**
     * @param string[] $filePaths
     * @return File[]
     */
    public function createFromPaths(array $filePaths) : array
    {
        $files = [];
        foreach ($filePaths as $filePath) {
            $files[] = new File($filePath, FileSystem::read($filePath));
        }
        return $files;
    }
    /**
     * @return string[]
     */
    private function resolveSupportedFileExtensions(Configuration $configuration) : array
    {
        $supportedFileExtensions = [];
        foreach ($this->fileProcessors as $fileProcessor) {
            $supportedFileExtensions = \array_merge($supportedFileExtensions, $fileProcessor->getSupportedFileExtensions());
        }
        // basic PHP extensions
        $supportedFileExtensions = \array_merge($supportedFileExtensions, $configuration->getFileExtensions());
        return \array_unique($supportedFileExtensions);
    }
}
