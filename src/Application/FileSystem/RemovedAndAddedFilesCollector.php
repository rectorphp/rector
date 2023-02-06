<?php

declare (strict_types=1);
namespace Rector\Core\Application\FileSystem;

use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Application\MovedFile;
use Rector\FileSystemRector\Contract\AddedFileInterface;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Rector\FileSystemRector\ValueObject\AddedFileWithNodes;
final class RemovedAndAddedFilesCollector
{
    /**
     * @var string[]
     */
    private $removedFilePaths = [];
    /**
     * @var AddedFileInterface[]
     */
    private $addedFiles = [];
    /**
     * @var MovedFile[]
     */
    private $movedFiles = [];
    public function removeFile(string $filePath) : void
    {
        $this->removedFilePaths[] = $filePath;
    }
    /**
     * @return string[]
     */
    public function getRemovedFiles() : array
    {
        return $this->removedFilePaths;
    }
    public function isFileRemoved(string $filePath) : bool
    {
        foreach ($this->removedFilePaths as $removedFilePath) {
            if ($removedFilePath !== $filePath) {
                continue;
            }
            return \true;
        }
        foreach ($this->movedFiles as $movedFile) {
            $file = $movedFile->getFile();
            if ($movedFile->getFilePath() !== $file->getFilePath()) {
                continue;
            }
            return \true;
        }
        return \false;
    }
    /**
     * @api
     */
    public function addAddedFile(AddedFileInterface $addedFile) : void
    {
        $this->addedFiles[] = $addedFile;
    }
    /**
     * @return AddedFileWithContent[]
     */
    public function getAddedFilesWithContent() : array
    {
        return \array_filter($this->addedFiles, static function (AddedFileInterface $addedFile) : bool {
            return $addedFile instanceof AddedFileWithContent;
        });
    }
    /**
     * @return AddedFileWithNodes[]
     */
    public function getAddedFilesWithNodes() : array
    {
        return \array_filter($this->addedFiles, static function (AddedFileInterface $addedFile) : bool {
            return $addedFile instanceof AddedFileWithNodes;
        });
    }
    public function getAddedFileCount() : int
    {
        return \count($this->addedFiles);
    }
    public function getRemovedFilesCount() : int
    {
        return \count($this->removedFilePaths);
    }
    /**
     * @api For testing
     */
    public function reset() : void
    {
        $this->addedFiles = [];
        $this->movedFiles = [];
        $this->removedFilePaths = [];
    }
    public function addMovedFile(File $file, string $newPathName) : void
    {
        $this->movedFiles[] = new MovedFile($file, $newPathName);
    }
    /**
     * @return MovedFile[]
     */
    public function getMovedFiles() : array
    {
        return $this->movedFiles;
    }
}
