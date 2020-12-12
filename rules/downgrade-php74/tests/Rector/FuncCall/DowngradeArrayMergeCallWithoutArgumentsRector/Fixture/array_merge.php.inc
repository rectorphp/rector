<?php

namespace Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeArrayMergeCallWithoutArgumentsRector\Fixture;

class ArrayMerge
{
    public function run()
    {
        array_merge();
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeArrayMergeCallWithoutArgumentsRector\Fixture;

class ArrayMerge
{
    public function run()
    {
        array_merge([]);
    }
}

?>
