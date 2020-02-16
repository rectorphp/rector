<?php

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

use Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Source\CountableClass;

trait VariableWithinTraitMethod
{
    public function run()
    {
        $array = [];
        $countable = new CountableClass();

        return
            count($array)
            +
            count($countable)
        ;
    }
}
