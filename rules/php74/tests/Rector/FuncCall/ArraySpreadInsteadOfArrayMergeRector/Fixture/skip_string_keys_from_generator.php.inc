<?php

namespace Rector\Php74\Tests\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector\Fixture;

function z(): \Generator
{
    yield 'key' => 'value';
}

class SkipStringKeysFromGenerator
{
    public function run()
    {
        return \iterator_to_array(z());
    }
}
