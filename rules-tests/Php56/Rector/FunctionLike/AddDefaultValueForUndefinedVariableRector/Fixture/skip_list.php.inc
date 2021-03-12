<?php

namespace Rector\Tests\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class SkipList
{
    public function run()
    {
        list($value, $value2) = explode('#', 'aoj_sdf');
        var_dump($value, $value2);
    }
}
