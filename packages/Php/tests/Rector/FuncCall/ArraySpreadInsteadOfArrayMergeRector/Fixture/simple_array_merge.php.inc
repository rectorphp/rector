<?php

namespace Rector\Php\Tests\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector\Fixture;

class SimpleArrayMerge
{
    public function run($iter1, $iter2)
    {
        $values = array_merge($iter1, $iter2);
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector\Fixture;

class SimpleArrayMerge
{
    public function run($iter1, $iter2)
    {
        $values = [...$iter1, ...$iter2];
    }
}

?>
