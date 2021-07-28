<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeThrowExprRector\Fixture;

class SkipNoThrowInAssign
{
    public function run($b)
    {
        $a = $b;
    }
}

?>
