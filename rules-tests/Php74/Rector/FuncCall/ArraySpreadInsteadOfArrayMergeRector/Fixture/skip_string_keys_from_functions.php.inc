<?php

namespace Rector\Tests\Php74\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector\Fixture;

function x(): array
{
    return ['b'=>1];
}
function y(): array
{
    return ['a'=>1];
}

class SkipStringKeysFromFunctions
{
    public function run()
    {
        return array_merge(y(), x());
    }
}
