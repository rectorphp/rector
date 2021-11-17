<?php

namespace Rector\Tests\DowngradePhp55\Rector\Foreach_\DowngradeForeachListRector\Fixture;

final class SomeClass
{
    public function run(array $array)
    {
        foreach ($array as $key => list($item1, $item2)) {
            var_dump($item1, $item2);
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp55\Rector\Foreach_\DowngradeForeachListRector\Fixture;

final class SomeClass
{
    public function run(array $array)
    {
        foreach ($array as $key => $arrayItem) {
            list($item1, $item2) = $arrayItem;
            var_dump($item1, $item2);
        }
    }
}

?>
