<?php

namespace Rector\Tests\DeadCode\Rector\StmtsAwareInterface\RemoveJustPropertyFetchRector\Fixture;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;

final class SkipDimFetchAssign
{
    private $items;

    public function run(Array_ $array)
    {
        /** @var ArrayItem[] $items */
        $items = $array->items;

        // $this, self, static, FQN
        $firstItemValue = $items[0]->value;
    }
}
