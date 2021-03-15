<?php

namespace Rector\Tests\DowngradePhp74\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\Fixture;

class SkipEmpty
{
    public function run($string)
    {
        // Empty: do no change
        strip_tags($string);
    }
}

?>
