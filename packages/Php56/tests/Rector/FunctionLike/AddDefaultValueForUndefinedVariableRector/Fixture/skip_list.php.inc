<?php

namespace Rector\Php56\Tests\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class SkipList
{
    public function run()
    {
        list($value, $value2) = explode('#', 'aoj_sdf');
        var_dump($value, $value2);
    }
}
