<?php

namespace Rector\Tests\DeadCode\Rector\FunctionLike\RemoveDuplicatedIfReturnRector\Fixture;

class SkipArrayShiftedValue
{
    public function output(array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        $widths = [];

        if (mt_rand(0, 100)) {
            array_shift($rows);
        }

        if (empty($rows)) {
            return;
        }
    }
}
