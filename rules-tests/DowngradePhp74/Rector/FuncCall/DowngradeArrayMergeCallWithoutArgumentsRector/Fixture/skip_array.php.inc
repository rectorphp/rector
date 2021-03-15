<?php

namespace Rector\Tests\DowngradePhp74\Rector\FuncCall\DowngradeArrayMergeCallWithoutArgumentsRector\Fixture;

class SkipArray
{
    public function run()
    {
        // Array: do no change
        array_merge([]);
        array_merge_recursive([]);
    }
}

?>
