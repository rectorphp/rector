<?php

declare(strict_types=1);

namespace Rector\Core\Bootstrap;

use Symplify\SmartFileSystem\SmartFileInfo;

final class ConfigShifter
{
    /**
     * Shift input config as last, so the parameters override previous rules loaded from sets
     *
     * @param SmartFileInfo[] $configFileInfos
     * @return SmartFileInfo[]
     * @noRector
     */
    public function shiftInputConfigAsLast(array $configFileInfos, ?SmartFileInfo $inputConfigFileInfo): array
    {
        if ($inputConfigFileInfo === null) {
            return $configFileInfos;
        }

        $mainConfigShiftedAsLast = [];
        foreach ($configFileInfos as $configFileInfo) {
            if ($configFileInfo !== $inputConfigFileInfo) {
                $mainConfigShiftedAsLast[] = $configFileInfo;
            }
        }

        $mainConfigShiftedAsLast[] = $inputConfigFileInfo;

        return $mainConfigShiftedAsLast;
    }
}
