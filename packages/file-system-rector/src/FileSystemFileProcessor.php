<?php

declare(strict_types=1);

namespace Rector\FileSystemRector;

use Rector\FileSystemRector\Contract\FileSystemRectorInterface;
use Rector\NodeTypeResolver\FileSystem\CurrentFileInfoProvider;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FileSystemFileProcessor
{
    /**
     * @var CurrentFileInfoProvider
     */
    private $currentFileInfoProvider;

    /**
     * @var FileSystemRectorInterface[]
     */
    private $fileSystemRectors = [];

    /**
     * @param FileSystemRectorInterface[] $fileSystemRectors
     */
    public function __construct(CurrentFileInfoProvider $currentFileInfoProvider, array $fileSystemRectors = [])
    {
        $this->currentFileInfoProvider = $currentFileInfoProvider;
        $this->fileSystemRectors = $fileSystemRectors;
    }

    public function processFileInfo(SmartFileInfo $smartFileInfo): void
    {
        $this->currentFileInfoProvider ->setCurrentFileInfo($smartFileInfo);

        foreach ($this->fileSystemRectors as $fileSystemRector) {
            $fileSystemRector->refactor($smartFileInfo);
        }
    }

    public function getFileSystemRectorsCount(): int
    {
        return count($this->fileSystemRectors);
    }
}
