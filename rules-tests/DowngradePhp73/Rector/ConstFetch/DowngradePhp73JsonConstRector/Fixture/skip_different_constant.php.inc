<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp73\Rector\FuncCall\DowngradePhp73JsonConstRector\Fixture;

class SkipDifferentConstant
{
    public function run()
    {
        json_encode($a, JSON_HEX_TAG);
    }
}
