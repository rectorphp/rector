<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

final class SkipUsedInPropertyFetch
{
    public function run()
    {
        $property = 'propertyName';
        $this->{$property} = 1;
    }
}
