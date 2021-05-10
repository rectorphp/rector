<?php

declare (strict_types=1);
namespace Rector\Core\Application\FileSystem;

use Rector\FileSystemRector\Contract\AddedFileInterface;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Rector\FileSystemRector\ValueObject\AddedFileWithNodes;
use Symplify\SmartFileSystem\SmartFileInfo;
final class RemovedAndAddedFilesCollector
{
    /**
     * @var SmartFileInfo[]
     */
    private $removedFileInfos = [];
    /**
     * @var AddedFileInterface[]
     */
    private $addedFiles = [];
    public function removeFile(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : void
    {
        $this->removedFileInfos[] = $smartFileInfo;
    }
    /**
     * @return SmartFileInfo[]
     */
    public function getRemovedFiles() : array
    {
        return $this->removedFileInfos;
    }
    public function isFileRemoved(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : bool
    {
        foreach ($this->removedFileInfos as $removedFileInfo) {
            if ($removedFileInfo->getPathname() !== $smartFileInfo->getPathname()) {
                continue;
            }
            return \true;
        }
        return \false;
    }
    public function addAddedFile(\Rector\FileSystemRector\Contract\AddedFileInterface $addedFile) : void
    {
        $this->addedFiles[] = $addedFile;
    }
    /**
     * @return AddedFileWithContent[]
     */
    public function getAddedFilesWithContent() : array
    {
        return \array_filter($this->addedFiles, function (\Rector\FileSystemRector\Contract\AddedFileInterface $addedFile) : bool {
            return $addedFile instanceof \Rector\FileSystemRector\ValueObject\AddedFileWithContent;
        });
    }
    /**
     * @return AddedFileWithNodes[]
     */
    public function getAddedFilesWithNodes() : array
    {
        return \array_filter($this->addedFiles, function (\Rector\FileSystemRector\Contract\AddedFileInterface $addedFile) : bool {
            return $addedFile instanceof \Rector\FileSystemRector\ValueObject\AddedFileWithNodes;
        });
    }
    public function getAffectedFilesCount() : int
    {
        return \count($this->addedFiles) + \count($this->removedFileInfos);
    }
    public function getAddedFileCount() : int
    {
        return \count($this->addedFiles);
    }
    public function getRemovedFilesCount() : int
    {
        return \count($this->removedFileInfos);
    }
    /**
     * For testing
     */
    public function reset() : void
    {
        $this->addedFiles = [];
        $this->removedFileInfos = [];
    }
}
