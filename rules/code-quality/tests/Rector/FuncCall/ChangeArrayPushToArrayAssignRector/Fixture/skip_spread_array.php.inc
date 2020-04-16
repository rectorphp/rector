<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\ChangeArrayPushToArrayAssignRector\Fixture;

final class SkipSpreadArray
{
    public function run()
    {
        $items = [1];
        $items2 = [2,3];
        array_push($items, ...$items2);
    }
}
