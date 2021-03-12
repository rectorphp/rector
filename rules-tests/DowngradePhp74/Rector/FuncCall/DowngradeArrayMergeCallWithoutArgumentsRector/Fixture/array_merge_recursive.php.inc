<?php

namespace Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeArrayMergeCallWithoutArgumentsRector\Fixture;

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

namespace Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeArrayMergeCallWithoutArgumentsRector\Fixture;

class ArrayMergeRecursive
{
    public function run()
    {
        array_merge_recursive([]);
    }
}

?>
