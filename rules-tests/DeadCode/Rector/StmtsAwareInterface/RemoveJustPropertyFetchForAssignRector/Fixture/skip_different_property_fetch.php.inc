<?php

namespace Rector\Tests\DeadCode\Rector\StmtsAwareInterface\RemoveJustPropertyFetchForAssignRector\Fixture;

final class SkipDifferentPropertyFetch
{
    private $items = [];

    private $another = [];

    public function run()
    {
        $items = $this->another;
        $items[] = 1000;
        $this->items = $items ;
    }
}
