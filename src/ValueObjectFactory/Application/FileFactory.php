<?php

declare (strict_types=1);
namespace Rector\Core\ValueObjectFactory\Application;

use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\FileSystem\FilesFinder;
use Rector\Core\ValueObject\Application\File;
/**
 * @see \Rector\Core\ValueObject\Application\File
 */
final class FileFactory
{
    /**
     * @var \Rector\Core\FileSystem\FilesFinder
     */
    private $filesFinder;
    /**
     * @var \Rector\Core\Configuration\Configuration
     */
    private $configuration;
    /**
     * @var \Rector\Caching\Detector\ChangedFilesDetector
     */
    private $changedFilesDetector;
    /**
     * @var mixed[]
     */
    private $fileProcessors;
    /**
     * @param FileProcessorInterface[] $fileProcessors
     */
    public function __construct(\Rector\Core\FileSystem\FilesFinder $filesFinder, \Rector\Core\Configuration\Configuration $configuration, \Rector\Caching\Detector\ChangedFilesDetector $changedFilesDetector, array $fileProcessors)
    {
        $this->filesFinder = $filesFinder;
        $this->configuration = $configuration;
        $this->changedFilesDetector = $changedFilesDetector;
        $this->fileProcessors = $fileProcessors;
    }
    /**
     * @param string[] $paths
     * @return File[]
     */
    public function createFromPaths(array $paths) : array
    {
        if ($this->configuration->shouldClearCache()) {
            $this->changedFilesDetector->clear();
        }
        $supportedFileExtensions = $this->resolveSupportedFileExtensions();
        $fileInfos = $this->filesFinder->findInDirectoriesAndFiles($paths, $supportedFileExtensions);
        $files = [];
        foreach ($fileInfos as $fileInfo) {
            $files[] = new \Rector\Core\ValueObject\Application\File($fileInfo, $fileInfo->getContents());
        }
        return $files;
    }
    /**
     * @return string[]
     */
    private function resolveSupportedFileExtensions() : array
    {
        $supportedFileExtensions = [];
        foreach ($this->fileProcessors as $fileProcessor) {
            $supportedFileExtensions = \array_merge($supportedFileExtensions, $fileProcessor->getSupportedFileExtensions());
        }
        return \array_unique($supportedFileExtensions);
    }
}
