<?php

namespace Rector\Tests\CodeQuality\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector\Source\IteratorAggregateImplementation;

final class SkipForeachWhenIteratedItemIsIteratorAggregate
{
    public function getItems(): array
    {
        $iterator = new IteratorAggregateImplementation();
        $items = [];
        foreach($iterator as $item) {
        	$items[] = $item;
        }
        return $items;
    }
}
?>
