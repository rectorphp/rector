<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\ConsistentImplodeRector\Fixture;

class SkipAlreadySwitched
{
    /**
     * @param string[] $versionedSets
     */
    public function createSetListInString(array $versionedSets): string
    {
        $setsListInString = '';

        foreach ($versionedSets as $groupName => $configName) {
            $setsListInString .= ' * ' . $groupName . ': ' . implode(', ', $configName) . PHP_EOL;
        }

        return $setsListInString;
    }
}
