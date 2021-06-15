<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

final class SkipArrayCallablesThisCaseInsensitiveMethodName
{
    public function run()
    {
        $array  = [3, 2, 1];

        usort($array, [$this, 'sortCamelCasedMethodName']);
        usort($array, [$this, 'sortCaseInSensitiveMETHODName']);

        return $array;
    }

    private function sortCamelCasedMethodName($a, $b)
    {
        return $a <=> $b;
    }

    private function SOrtCAseINSENsitivEMEthodnaME($a, $b)
    {
        return $a <=> $b;
    }
}
