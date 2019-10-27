<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\ArrayMergeOfNonArraysToSimpleArrayRector\Fixture;

class NestedArrays
{
    public function go()
    {
        $values = [1, 2, 3];
        $values2 = [3, 5, 7];

        return array_merge([$values], [$values2]);
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\ArrayMergeOfNonArraysToSimpleArrayRector\Fixture;

class NestedArrays
{
    public function go()
    {
        $values = [1, 2, 3];
        $values2 = [3, 5, 7];

        return [$values, $values2];
    }
}

?>
