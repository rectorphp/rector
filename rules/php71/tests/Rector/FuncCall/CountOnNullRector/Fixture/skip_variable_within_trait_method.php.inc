<?php

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

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
