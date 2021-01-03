<?php

namespace Rector\Php71\Tests\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

use Rector\Php71\Tests\Rector\FuncCall\RemoveExtraParametersRector\Source\MethodWithFuncGetArgs;

class SkipExternalScope
{
    function run()
    {
        MethodWithFuncGetArgs::call(1, 2, 3);
    }
}
