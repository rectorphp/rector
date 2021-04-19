<?php

namespace Rector\Tests\DowngradePhp71\Rector\List_\DowngradeKeysInListRector\Fixture;

class SkipNotKeysInList2
{
    public function run(): void
    {
        $data = ['foo', 'bar', 'baz'];
        list(, $bar, $baz) = $data;
    }
}

?>

