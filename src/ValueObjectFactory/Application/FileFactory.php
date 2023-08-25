<?php

declare (strict_types=1);
namespace Rector\Core\ValueObjectFactory\Application;

use Rector\Caching\Detector\ChangedFilesDetector;
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
    public function __construct(FilesFinder $filesFinder, ChangedFilesDetector $changedFilesDetector)
    {
        $this->filesFinder = $filesFinder;
        $this->changedFilesDetector = $changedFilesDetector;
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
        $supportedFileExtensions = $configuration->getFileExtensions();
        $filePaths = $this->filesFinder->findInDirectoriesAndFiles($paths, $supportedFileExtensions);
        $fileWithExtensionsFilter = static function (string $filePath) use($supportedFileExtensions) : bool {
            $filePathExtension = \pathinfo($filePath, \PATHINFO_EXTENSION);
            return \in_array($filePathExtension, $supportedFileExtensions, \true);
        };
        return \array_filter($filePaths, $fileWithExtensionsFilter);
    }
}
