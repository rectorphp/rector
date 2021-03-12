<?php

namespace Rector\Tests\Php72\Rector\Assign\ReplaceEachAssignmentWithKeyCurrentRector\Fixture;

final class SkipWhile
{
    public function run()
    {
        $array = ['a' => 1, 'b' => 2];

        $list = [];
        while ($first = each($array)) {
            $list[] = $first;
        }
    }
}
