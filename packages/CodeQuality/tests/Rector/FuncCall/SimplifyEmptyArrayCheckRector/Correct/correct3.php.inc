<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\FuncCall\SimplifyEmptyArrayCheckRector\Wrong;

class SomeClass
{
    public function isntEmptyArray($values): bool
    {
        return is_array($values) && !empty($values);
    }

    public function isntEmptyArray2($values): bool
    {
        return !empty($values) && is_array($values);
    }

    public function twoNegatives($values): bool
    {
        return !empty($values->foo) && !is_array($values->bar);
    }

}

$objects = [];
is_array($objects) && is_array($objects);
