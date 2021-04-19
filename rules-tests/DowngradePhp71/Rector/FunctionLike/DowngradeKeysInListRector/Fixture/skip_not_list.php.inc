<?php

namespace Rector\Tests\DowngradePhp71\Rector\FunctionLike\DowngradeKeysInListRector\Fixture;

class SkipNotList
{
    public function run()
    {
        strlen('abc');
    }
}

?>
