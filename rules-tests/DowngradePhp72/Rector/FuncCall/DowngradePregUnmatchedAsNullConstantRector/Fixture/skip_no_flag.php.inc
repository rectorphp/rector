<?php

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector\Fixture;

class SkipNoFlag
{
    public function run()
    {
        preg_match('/(a)(b)*(c)/', 'ac', $matches);
    }
}

?>

