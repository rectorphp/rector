<?php

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Fixture;

use Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Source\CountableClass;

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
