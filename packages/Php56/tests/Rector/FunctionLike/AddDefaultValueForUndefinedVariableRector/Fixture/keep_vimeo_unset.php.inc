<?php

namespace Rector\Php56\Tests\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class KeepVimeoUnset
{
    public function run()
    {
        if (rand(0, 1)) {
            $a = "bar";
        }
        unset($a);
    }
}
