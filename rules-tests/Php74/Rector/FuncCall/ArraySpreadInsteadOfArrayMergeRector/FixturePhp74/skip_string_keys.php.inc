<?php

namespace Rector\Tests\Php74\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector\FixturePhp74;

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
