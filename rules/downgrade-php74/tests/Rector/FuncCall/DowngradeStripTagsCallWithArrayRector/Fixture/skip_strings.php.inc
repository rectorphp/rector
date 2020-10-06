<?php

namespace Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\Fixture;

class SkipStringClass
{
    public function run($string)
    {
        // Strings: do no change
        strip_tags($string, '<a><p>');
    }
}

?>
