<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp71\Rector\FuncCall\DowngradePhp71JsonConstRector\Fixture;

class SkipBitwiseDifferentConstant
{
    public function run($argument)
    {
        $argument = json_encode($argument, JSON_HEX_TAG | JSON_PRETTY_PRINT);
    }
}
