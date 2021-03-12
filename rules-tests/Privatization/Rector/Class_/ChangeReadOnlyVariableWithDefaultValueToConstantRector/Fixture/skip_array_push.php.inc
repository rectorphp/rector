<?php

namespace Rector\Tests\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

final class SkipArrayPush
{
    public function run(): string
    {
        $sortedNodes = [];
        array_push($sortedNodes, 1);

        return $sortedNodes;
    }
}
