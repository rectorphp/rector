<?php

namespace Rector\Privatization\Tests\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

final class SkipArrayPush
{
    public function run(): string
    {
        $sortedNodes = [];
        array_push($sortedNodes, 1);

        return $sortedNodes;
    }
}
