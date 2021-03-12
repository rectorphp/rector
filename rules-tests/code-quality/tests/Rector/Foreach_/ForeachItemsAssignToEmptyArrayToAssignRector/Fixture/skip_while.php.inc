<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector\Fixture;

class SkipWhile
{
    public function run($items)
    {
        $items2 = [];
        while (true) {
            foreach ($items as $item) {
                $items2[] = $item;
            }
        }
    }
}
