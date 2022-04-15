<?php

namespace Rector\Tests\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class SkipWithCoalesceAssign
{
    public function run()
    {
        if (rand(0, 1)) {
            $a = 'a';
        }

        $a ??= 'test';
    }
}
