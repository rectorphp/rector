<?php

use Rector\Php\Tests\Rector\FuncCall\RemoveExtraParametersRector\Source\MethodWithFuncGetArgs;

function removeExtaParametersExternalScope()
{
    MethodWithFuncGetArgs::call(1, 2, 3);
}
