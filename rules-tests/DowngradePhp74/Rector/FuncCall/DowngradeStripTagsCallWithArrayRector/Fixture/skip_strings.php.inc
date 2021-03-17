<?php

namespace Rector\Tests\DowngradePhp74\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\Fixture;

class SkipStrings
{
    public function run($string)
    {
        // Strings: do no change
        strip_tags($string, '<a><p>');
    }
}

?>
