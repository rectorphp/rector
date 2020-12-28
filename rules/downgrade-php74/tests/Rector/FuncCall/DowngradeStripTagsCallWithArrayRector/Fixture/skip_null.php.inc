<?php

namespace Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\Fixture;

class SkipNull
{
    public function run($string)
    {
        // Null: do no change
        strip_tags($string, null);
    }
}

?>
