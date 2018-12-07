<?php declare(strict_types=1);

namespace Rector\Application;

use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

final class FilesToReprintCollector
{
    /**
     * @var SmartFileInfo[]
     */
    private $fileInfos = [];

    public function addFileInfoWithNewTokens(SmartFileInfo $smartFileInfo): void
    {
        $this->fileInfos[$smartFileInfo->getRealPath()] = $smartFileInfo;
    }

    /**
     * @return SmartFileInfo[]
     */
    public function getFileInfos(): array
    {
        return $this->fileInfos;
    }

    public function reset(): void
    {
        $this->fileInfos = [];
    }
}
