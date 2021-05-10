<?php

declare (strict_types=1);
namespace Rector\Core\ValueObjectFactory\Application;

use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\FileSystem\FilesFinder;
use Rector\Core\ValueObject\Application\File;
/**
 * @see \Rector\Core\ValueObject\Application\File
 */
final class FileFactory
{
    /**
     * @var FileProcessorInterface[]
     */
    private $fileProcessors = [];
    /**
     * @var FilesFinder
     */
    private $filesFinder;
    /**
     * @param FileProcessorInterface[] $fileProcessors
     */
    public function __construct(\Rector\Core\FileSystem\FilesFinder $filesFinder, array $fileProcessors)
    {
        $this->fileProcessors = $fileProcessors;
        $this->filesFinder = $filesFinder;
    }
    /**
     * @param string[] $paths
     * @return File[]
     */
    public function createFromPaths(array $paths) : array
    {
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
