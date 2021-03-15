<?php

namespace Rector\Tests\DowngradePhp74\Rector\FuncCall\DowngradeArrayMergeCallWithoutArgumentsRector\Fixture;

class ArrayMergeRecursive
{
    public function run()
    {
        array_merge_recursive();
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\FuncCall\DowngradeArrayMergeCallWithoutArgumentsRector\Fixture;

class ArrayMergeRecursive
{
    public function run()
    {
        array_merge_recursive([]);
    }
}

?>
