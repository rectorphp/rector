<?php

namespace Rector\Tests\Php72\Rector\Assign\ListEachRector\Fixture;

final class ListEachNext
{
    public function run()
    {
        $parentArray = ['a' => 1, 'b' => 2];

        list($key, $value) = each($parentArray);

        list($key2, $value2) = each($parentArray);

        return [$key, $value, $parentArray, $key2, $value2];
    }
}

?>
-----
<?php

namespace Rector\Tests\Php72\Rector\Assign\ListEachRector\Fixture;

final class ListEachNext
{
    public function run()
    {
        $parentArray = ['a' => 1, 'b' => 2];

        $key = key($parentArray);
        $value = current($parentArray);
        next($parentArray);

        $key2 = key($parentArray);
        $value2 = current($parentArray);
        next($parentArray);

        return [$key, $value, $parentArray, $key2, $value2];
    }
}

?>
