<?php

namespace Rector\SOLID\Tests\Rector\ClassMethod\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

final class SkipArrayPush
{
    public function run(): string
    {
        $sortedNodes = [];
        array_push($sortedNodes, 1);

        return $sortedNodes;
    }
}
