<?php

declare (strict_types=1);
namespace Rector\Core\ValueObjectFactory\Application;

use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\FileSystem\FilesFinder;
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
    public function __construct(FilesFinder $filesFinder, ChangedFilesDetector $changedFilesDetector, iterable $fileProcessors)
    {
        $this->filesFinder = $filesFinder;
        $this->changedFilesDetector = $changedFilesDetector;
        $this->fileProcessors = $fileProcessors;
    }
    /**
     * @param string[] $paths
     * @return string[]
     */
    public function findFilesInPaths(array $paths, Configuration $configuration) : array
    {
        if ($configuration->shouldClearCache()) {
            $this->changedFilesDetector->clear();
        }
        $supportedFileExtensions = $this->resolveSupportedFileExtensions($configuration);
        $filePaths = $this->filesFinder->findInDirectoriesAndFiles($paths, $supportedFileExtensions);
        $fileExtensions = $configuration->getFileExtensions();
        $fileWithExtensionsFilter = static function (string $filePath) use($fileExtensions) : bool {
            $filePathExtension = \pathinfo($filePath, \PATHINFO_EXTENSION);
            return \in_array($filePathExtension, $fileExtensions, \true);
        };
        return \array_filter($filePaths, $fileWithExtensionsFilter);
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
