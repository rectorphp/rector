<?php

namespace Rector\Tests\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class SkipCoalesce
{
    public function run()
    {
        if (rand(0,1)) {
            $value = 'value';
        }

        return $value ?? 'test';
    }
}
