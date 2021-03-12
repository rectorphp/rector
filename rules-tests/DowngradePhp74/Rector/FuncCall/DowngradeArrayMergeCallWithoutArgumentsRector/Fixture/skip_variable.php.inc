<?php

namespace Rector\Tests\DowngradePhp74\Rector\FuncCall\DowngradeArrayMergeCallWithoutArgumentsRector\Fixture;

class SkipVariable
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
