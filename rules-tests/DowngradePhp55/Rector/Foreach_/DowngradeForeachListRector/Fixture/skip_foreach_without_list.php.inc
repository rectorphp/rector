<?php

namespace Rector\Tests\DowngradePhp55\Rector\Foreach_\DowngradeForeachListRector\Fixture;

final class SomeClass
{
    public function run(array $array)
    {
        foreach ($array as $key => $item) {
            var_dump($item);
        }
    }
}

?>
