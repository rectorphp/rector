<?php

namespace Rector\Tests\DowngradePhp71\Rector\List_\DowngradeKeysInListRector\Fixture;

class SkipNotKeysInList
{
    public function run(): void
    {
        $data = ['foo', 'bar', 'baz'];
        list($foo, $bar, $baz) = $data;
    }
}

?>

