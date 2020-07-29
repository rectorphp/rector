<?php

declare(strict_types=1);

namespace Rector\FileSystemRector;

use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Logging\CurrentRectorProvider;
use Rector\Core\Testing\Application\EnabledRectorsProvider;
use Rector\FileSystemRector\Contract\FileSystemRectorInterface;
use Rector\NodeTypeResolver\FileSystem\CurrentFileInfoProvider;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FileSystemFileProcessor
{
    /**
     * @var FileSystemRectorInterface[]
     */
    private $fileSystemRectors = [];

    /**
     * @var CurrentFileInfoProvider
     */
    private $currentFileInfoProvider;

    /**
     * @var CurrentRectorProvider
     */
    private $currentRectorProvider;

    /**
     * @var EnabledRectorsProvider
     */
    private $enabledRectorsProvider;

    /**
     * @param FileSystemRectorInterface[] $fileSystemRectors
     */
    public function __construct(
        CurrentFileInfoProvider $currentFileInfoProvider,
        CurrentRectorProvider $currentRectorProvider,
        EnabledRectorsProvider $enabledRectorsProvider,
        array $fileSystemRectors = []
    ) {
        $this->currentFileInfoProvider = $currentFileInfoProvider;
        $this->fileSystemRectors = $fileSystemRectors;
        $this->currentRectorProvider = $currentRectorProvider;
        $this->enabledRectorsProvider = $enabledRectorsProvider;
    }

    public function processFileInfo(SmartFileInfo $smartFileInfo): void
    {
        $this->currentFileInfoProvider->setCurrentFileInfo($smartFileInfo);

        foreach ($this->fileSystemRectors as $fileSystemRector) {
            if (! $this->enabledRectorsProvider->isRectorActive($fileSystemRector)) {
                continue;
            }

            if ($fileSystemRector instanceof ConfigurableRectorInterface) {
                $configuration = $this->enabledRectorsProvider->getRectorConfiguration($fileSystemRector);
                $fileSystemRector->configure($configuration);
            }

            $this->currentRectorProvider->changeCurrentRector($fileSystemRector);
            $fileSystemRector->refactor($smartFileInfo);
        }
    }

    public function getFileSystemRectorsCount(): int
    {
        return count($this->fileSystemRectors);
    }
}
