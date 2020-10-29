<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector\Fixture;

class SkipFor
{
    public function run($items)
    {
        $items2 = [];
        for ($i = 100; $i < 1000; $i++) {
            foreach ($items as $item) {
                $items2[] = $item;
            }
        }
    }
}
