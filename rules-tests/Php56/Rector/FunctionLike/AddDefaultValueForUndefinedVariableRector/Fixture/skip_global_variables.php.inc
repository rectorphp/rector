<?php

namespace Rector\Tests\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class SkipGlobalVariables
{
    public function run()
    {
        global $a;
        $a = 5;
    }
}
