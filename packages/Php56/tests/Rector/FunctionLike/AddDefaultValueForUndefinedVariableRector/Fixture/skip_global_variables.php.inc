<?php

namespace Rector\Php56\Tests\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class SkipGlobalVariables
{
    public function run()
    {
        global $a;
        $a = 5;
    }
}
