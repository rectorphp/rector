<?php

namespace Rector\Tests\DeadCode\Rector\StmtsAwareInterface\RemoveJustPropertyFetchForAssignRector\Fixture;

final class SkipDifferentVariable
{
    private $items = [];

    public function run()
    {
        $items = $this->items;
        $items[] = 1000;
        $this->items = $items2;
    }
}
