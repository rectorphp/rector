<?php

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector\Fixture;

class SkipNotRegexFunctions
{
    public function run()
    {
        strlen('test');
    }
}

?>

