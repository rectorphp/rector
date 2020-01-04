<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector\Fixture;

class SkipNonEmptyArray
{
    public function run($items)
    {
        $items2 = [1];
        foreach ($items as $item) {
             $items2[] = $item;
        }
    }
}
