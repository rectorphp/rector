<?php declare(strict_types=1);

namespace Rector\Application;

use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

final class RemovedFilesCollector
{
    /**
     * @var SmartFileInfo[]
     */
    private $files = [];

    public function addFile(SmartFileInfo $smartFileInfo): void
    {
        $this->files[$smartFileInfo->getRealPath()] = $smartFileInfo;
    }

    /**
     * @return SmartFileInfo[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    public function hasFile(SmartFileInfo $smartFileInfo): bool
    {
        return isset($this->files[$smartFileInfo->getRealPath()]);
    }
}
