<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector\Fixture;

class SkipNoSecondArg
{
    public function run(array $items)
    {
        array_push($items);
    }
}