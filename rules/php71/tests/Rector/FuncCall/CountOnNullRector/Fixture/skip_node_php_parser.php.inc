<?php

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

use PhpParser\Node\Expr\Array_;

final class SkipNodePhpParser
{
    public function run(Array_ $array)
    {
        if (count($array->items)) {
            return;
        }
    }
}
