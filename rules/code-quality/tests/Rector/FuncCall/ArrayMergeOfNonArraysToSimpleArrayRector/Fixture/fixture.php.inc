<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\ArrayMergeOfNonArraysToSimpleArrayRector\Fixture;

class Fixture
{
    public function go()
    {
        $value = 5;
        $value2 = 10;

        return array_merge([$value], [$value2]);
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\ArrayMergeOfNonArraysToSimpleArrayRector\Fixture;

class Fixture
{
    public function go()
    {
        $value = 5;
        $value2 = 10;

        return [$value, $value2];
    }
}

?>
