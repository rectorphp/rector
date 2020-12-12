<?php

namespace Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeArrayMergeCallWithoutArgumentsRector\Fixture;

class ArrayMergeClass
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

class ArrayMergeClass
{
    public function run()
    {
        array_merge([]);
    }
}

?>
