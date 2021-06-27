<?php

namespace Rector\Tests\Php71\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

use Rector\Tests\Php71\Rector\FuncCall\RemoveExtraParametersRector\Source\DibiWithVariadicDocMethod;

final class SkipVariadicDocMethod
{
    public function run(DibiWithVariadicDocMethod $dibiWithVariadicDocMethod)
    {
        $dibiWithVariadicDocMethod->where(1, 2, 3);
    }
}
