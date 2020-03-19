<?php

declare(strict_types=1);

namespace Rector\ChangesReporting\Collector;

use Rector\ChangesReporting\ValueObject\RectorWithFileAndLineChange;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RectorChangeCollector
{
    /**
     * @var RectorWithFileAndLineChange[]
     */
    private $rectorWithFileAndLineChanges = [];

    public function addRectorClassWithLine(string $rectorClass, SmartFileInfo $smartFileInfo, int $line): void
    {
        $this->rectorWithFileAndLineChanges[] = new RectorWithFileAndLineChange(
            $rectorClass,
            $smartFileInfo->getRealPath(),
            $line
        );
    }

    /**
     * @return RectorWithFileAndLineChange[]
     */
    public function getRectorChangesByFileInfo(SmartFileInfo $smartFileInfo): array
    {
        return array_filter(
            $this->rectorWithFileAndLineChanges,
            function (RectorWithFileAndLineChange $rectorWithFileAndLineChange) use ($smartFileInfo) {
                return $rectorWithFileAndLineChange->getRealPath() === $smartFileInfo->getRealPath();
            }
        );
    }
}
