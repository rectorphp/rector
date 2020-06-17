<?php

namespace Rector\Php72\Tests\Rector\Each\ReplaceEachAssignmentWithKeyCurrentRector\Fixture;

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
