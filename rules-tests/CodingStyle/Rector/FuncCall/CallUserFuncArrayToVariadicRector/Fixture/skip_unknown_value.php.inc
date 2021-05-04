<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\CallUserFuncArrayToVariadicRector\Fixture;

class SkipUnknownValue
{
    public function run($name)
    {
        call_user_func_array($name, $items);
    }
}
