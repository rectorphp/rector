<?php declare(strict_types=1);

namespace Rector\Application;

use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

final class FilesToReprintCollector
{
    /**
     * @var SmartFileInfo[]|string[]
     */
    private $items = [];

    public function addFileInfoAndRectorClass(SmartFileInfo $smartFileInfo, string $rectorClass): void
    {
        $this->items[$smartFileInfo->getRealPath()] = [$rectorClass, $smartFileInfo];
    }

    /**
     * @return SmartFileInfo[]|string[]
     */
    public function getFileInfosAndRectorClasses(): array
    {
        return $this->items;
    }

    public function reset(): void
    {
        $this->items = [];
    }
}
