<?php

namespace Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeArrayMergeCallWithoutArgumentsRector\Fixture;

class ArrayMergeRecursiveClass
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

class ArrayMergeRecursiveClass
{
    public function run()
    {
        array_merge_recursive([]);
    }
}

?>
