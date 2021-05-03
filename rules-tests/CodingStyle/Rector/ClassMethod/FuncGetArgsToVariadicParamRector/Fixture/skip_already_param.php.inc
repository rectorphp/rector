<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector\Fixture;

function skip_already_param($someParam)
{
    $args = \func_get_args();
}
