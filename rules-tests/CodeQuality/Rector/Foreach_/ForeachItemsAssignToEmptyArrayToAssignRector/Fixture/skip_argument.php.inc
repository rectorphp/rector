<?php

namespace Rector\Tests\CodeQuality\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector\Fixture;

class SkipArgument
{
    public function run($items, $items2)
    {
        foreach ($items as $item) {
             $items2[] = $item;
        }
    }
}
