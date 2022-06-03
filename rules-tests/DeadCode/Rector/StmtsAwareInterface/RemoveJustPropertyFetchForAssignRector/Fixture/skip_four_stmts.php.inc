<?php

namespace Rector\Tests\DeadCode\Rector\StmtsAwareInterface\RemoveJustPropertyFetchForAssignRector\Fixture;

final class SkipFourStmts
{
    private $items = [];

    public function run()
    {
        $items = $this->items;
        $items[] = 1000;
        $items[] = 1000;
        $this->items = $items ;
    }
}
