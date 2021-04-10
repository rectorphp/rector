<?php

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector\Fixture;

class SkipDifferentFlag2
{
    public function run()
    {
        preg_match('/(a)(b)*(c)/', 'ac', $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
    }
}

?>

