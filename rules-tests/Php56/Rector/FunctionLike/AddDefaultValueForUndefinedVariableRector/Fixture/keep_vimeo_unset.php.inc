<?php

namespace Rector\Tests\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

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
