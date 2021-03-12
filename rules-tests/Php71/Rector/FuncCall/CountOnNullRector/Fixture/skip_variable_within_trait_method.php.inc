<?php

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Fixture;

class SkipVariableWithintTraitMethod
{
    use VariableWithinTraitMethod;
}

trait VariableWithinTraitMethod
{
    public function run()
    {
        $array = [];
        return count($array);
    }
}
