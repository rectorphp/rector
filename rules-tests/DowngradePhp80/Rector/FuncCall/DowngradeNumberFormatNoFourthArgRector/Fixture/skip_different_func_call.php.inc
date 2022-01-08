<?php

namespace Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeNumberFormatNoFourthArgRector\Fixture;

class SkipDifferentFuncCall
{
    public function run(string $data)
    {
        return strlen($data);
    }
}
