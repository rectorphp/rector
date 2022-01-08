<?php

namespace Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeNumberFormatNoFourthArgRector\Fixture;

class SkipNoThirdArg
{
    public function run(float $data)
    {
        return number_format($data, 2);
    }
}
