<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp71\Rector\FuncCall\DowngradePhp71JsonConstRector\Fixture;

class SkipDifferentConstant
{
    public function run()
    {
        json_encode($a, JSON_HEX_TAG);
    }
}
