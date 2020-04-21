<?php

declare(strict_types=1);

namespace Rector\FileSystemRector;

use Rector\Core\Logging\CurrentRectorProvider;
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
     * @var CurrentRectorProvider
     */
    private $currentRectorProvider;

    /**
     * @param FileSystemRectorInterface[] $fileSystemRectors
     */
    public function __construct(
        CurrentFileInfoProvider $currentFileInfoProvider,
        CurrentRectorProvider $currentRectorProvider,
        array $fileSystemRectors = []
    ) {
        $this->currentFileInfoProvider = $currentFileInfoProvider;
        $this->fileSystemRectors = $fileSystemRectors;
        $this->currentRectorProvider = $currentRectorProvider;
    }

    public function processFileInfo(SmartFileInfo $smartFileInfo): void
    {
        $this->currentFileInfoProvider->setCurrentFileInfo($smartFileInfo);

        foreach ($this->fileSystemRectors as $fileSystemRector) {
            $this->currentRectorProvider->changeCurrentRector($fileSystemRector);

            $fileSystemRector->refactor($smartFileInfo);
        }
    }

    public function getFileSystemRectorsCount(): int
    {
        return count($this->fileSystemRectors);
    }
}
