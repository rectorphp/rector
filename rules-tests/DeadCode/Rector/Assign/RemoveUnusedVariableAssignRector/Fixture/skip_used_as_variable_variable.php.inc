<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

class SkipUsedAsVariableVariable
{
    public function run()
    {
        $value = 'a';
        $a     = $value;

        echo $$value;
        echo ${$value};
        echo "${$value}";
    }
}
