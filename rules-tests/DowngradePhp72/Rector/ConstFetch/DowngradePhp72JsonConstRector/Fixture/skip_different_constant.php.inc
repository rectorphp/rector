<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePhp72JsonConstRector\Fixture;

class SkipDifferentConstant
{
    public function run()
    {
        json_encode($a, JSON_HEX_TAG);
    }
}
