<?php declare(strict_types=1);

namespace Rector\Application;

use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

final class AppliedRectorCollector
{
    /**
     * @var string[][]
     */
    private $rectorClassesByFile = [];

    public function addRectorClass(string $rectorClass, SmartFileInfo $smartFileInfo): void
    {
        $this->rectorClassesByFile[$smartFileInfo->getRealPath()][] = $rectorClass;
    }

    /**
     * @return string[]
     */
    public function getRectorClasses(SmartFileInfo $smartFileInfo): array
    {
        if (isset($this->rectorClassesByFile[$smartFileInfo->getRealPath()])) {
            return array_unique($this->rectorClassesByFile[$smartFileInfo->getRealPath()]);
        }

        return [];
    }
}
