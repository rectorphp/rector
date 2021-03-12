<?php

namespace Rector\Php74\Tests\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector\Fixture;

class SkipStringKeys
{
    public function run()
    {
        $iter1 = ['one' => 'two'];
        $iter2 = ['three' => 'four'];

        return array_merge($iter1, $iter2);
    }

    public function go()
    {
        $iter1 = [1 => 'two'];
        $iter2 = ['three' => 'four'];

        return array_merge($iter1, $iter2);
    }
}
