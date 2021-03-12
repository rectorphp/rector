<?php

namespace Rector\Tests\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class SkipTakeStaticIntoAccount
{
    public function run()
    {
        static $variable = null;
        if (null === $variable) {
            $variable = 'not null anymore';
        }

        return $variable;
    }
}
