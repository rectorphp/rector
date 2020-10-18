<?php

namespace Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeArrayMergeCallWithoutArgumentsRector\Fixture;

class SkipArrayClass
{
    public function run()
    {
        // Array: do no change
        array_merge([]);
        array_merge_recursive([]);
    }
}

?>
