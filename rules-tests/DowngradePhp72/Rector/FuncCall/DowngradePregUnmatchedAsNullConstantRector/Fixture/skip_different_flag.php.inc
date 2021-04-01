<?php

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector\Fixture;

class SkipDifferentFlag
{
    public function run()
    {
        preg_match('/(a)(b)*(c)/', 'ac', $matches, PREG_OFFSET_CAPTURE);
    }
}

?>

