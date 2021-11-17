<?php

namespace Rector\Tests\DowngradePhp55\Rector\Foreach_\DowngradeForeachListRector\Fixture;

final class SomeClass
{
    public function run(array $array)
    {
        $arrayItem = ['foo'];
        foreach ($array as list($item1, $item2)) {
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
        $arrayItem = ['foo'];
        foreach ($array as $arrayItem2) {
            list($item1, $item2) = $arrayItem2;
            var_dump($item1, $item2);
        }
    }
}

?>
