<?php

namespace Rector\Tests\DowngradePhp72\Rector\Function_\DowngradePregUnmatchedAsNullConstantRector\Fixture;

class SkipNotRegexFunctions
{
    public function run()
    {
        strlen('test');
    }
}

?>

