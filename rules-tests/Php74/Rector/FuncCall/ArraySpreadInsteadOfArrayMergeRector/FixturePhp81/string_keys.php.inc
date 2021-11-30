<?php

namespace Rector\Tests\Php74\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector\FixturePhp81;

class StringKeys
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
?>
-----
<?php

namespace Rector\Tests\Php74\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector\FixturePhp81;

class StringKeys
{
    public function run()
    {
        $iter1 = ['one' => 'two'];
        $iter2 = ['three' => 'four'];

        return [...$iter1, ...$iter2];
    }

    public function go()
    {
        $iter1 = [1 => 'two'];
        $iter2 = ['three' => 'four'];

        return [...$iter1, ...$iter2];
    }
}
?>
