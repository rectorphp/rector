<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeThrowExprRector\Fixture;

use RuntimeException;

class SkipDirectThrow
{
    public function run()
    {
        throw new RuntimeException();
    }
}

?>
