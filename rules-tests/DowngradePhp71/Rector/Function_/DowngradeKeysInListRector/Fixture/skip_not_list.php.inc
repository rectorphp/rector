<?php

namespace Rector\Tests\DowngradePhp71\Rector\Function_\DowngradeKeysInListRector\Fixture;

class SkipNotList
{
    public function run()
    {
        strlen('abc');
    }
}

?>
