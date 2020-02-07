<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector\Fixture;

class SkipNestedForeachEdgeCase
{
    public function run($itemsOfItems)
    {
        $collectedItems = [];
        foreach ($itemsOfItems as $items) {
             foreach ($items as $item) {
                 $collectedItems[] = $item;
             }
        }
    }
}
