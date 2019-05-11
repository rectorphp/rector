<?php

namespace Rector\Php\Tests\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector\Fixture;

class IteratorToArray
{
    public function run($items)
    {
        $values = iterator_to_array($items);
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector\Fixture;

class IteratorToArray
{
    public function run($items)
    {
        $values = [...$items];
    }
}

?>
