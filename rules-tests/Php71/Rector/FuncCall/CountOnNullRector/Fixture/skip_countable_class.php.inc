<?php

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

use Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Source\CountableClass;

class SkipCountableClass
{
    public function run()
    {
        $values = [];
        $count = count($values);

        $values = new CountableClass();
        $count = count($values);
    }
}
