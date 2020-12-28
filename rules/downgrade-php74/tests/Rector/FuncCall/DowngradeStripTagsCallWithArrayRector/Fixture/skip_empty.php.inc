<?php

namespace Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\Fixture;

class SkipEmpty
{
    public function run($string)
    {
        // Empty: do no change
        strip_tags($string);
    }
}

?>
