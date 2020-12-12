<?php

namespace Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeArrayMergeCallWithoutArgumentsRector\Fixture;

class SkipVariableClass
{
    public function run()
    {
        $array = [];
        // Variable: do no change
        array_merge($array);
        array_merge_recursive($array);
    }
}

?>
