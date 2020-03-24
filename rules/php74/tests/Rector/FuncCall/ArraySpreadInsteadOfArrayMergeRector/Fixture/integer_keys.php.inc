<?php

namespace Rector\Php74\Tests\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector\Fixture;

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

namespace Rector\Php74\Tests\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector\Fixture;

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
