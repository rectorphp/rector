<?php

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

use Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Source\CountableClass;

class SomeClassUsingTrait
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
