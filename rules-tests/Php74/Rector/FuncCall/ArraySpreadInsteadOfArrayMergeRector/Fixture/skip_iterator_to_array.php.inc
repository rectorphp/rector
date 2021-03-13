<?php

namespace Rector\Tests\Php74\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector\Fixture;

final class SkipIteratorToArray
{
    public function run($items)
    {
        $values = iterator_to_array($items);
    }
}

?>
