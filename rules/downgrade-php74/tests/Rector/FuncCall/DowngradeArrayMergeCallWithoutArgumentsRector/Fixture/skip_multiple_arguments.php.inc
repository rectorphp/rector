<?php

namespace Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeArrayMergeCallWithoutArgumentsRector\Fixture;

class SkipMultipleArguments
{
    public function run()
    {
        $array = [];
        // Multiple Arguments: do no change
        array_merge([], []);
        array_merge($array, []);
        array_merge([], $array);
        array_merge($array, $array);
        array_merge_recursive([], []);
        array_merge_recursive($array, []);
        array_merge_recursive([], $array);
        array_merge_recursive($array, $array);
    }
}

?>
