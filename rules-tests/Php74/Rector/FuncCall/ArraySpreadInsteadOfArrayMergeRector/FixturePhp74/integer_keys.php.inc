<?php

namespace Rector\Tests\Php74\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector\FixturePhp74;

class IntegerKeys
{
    public function run()
    {
        $iter1 = [0 => 'two'];
        $iter2 = [1 => 'four'];

        return array_merge($iter1, $iter2);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector\FixturePhp74;

class IntegerKeys
{
    public function run()
    {
        $iter1 = [0 => 'two'];
        $iter2 = [1 => 'four'];

        return [...$iter1, ...$iter2];
    }
}

?>
