<?php

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Fixture;

final class SkipWithIfAlwaysArray
{
    public function run($a, $b)
    {
        $data = [];
        if (! $a) {
            $data[] = $b;
        }

        $data[] = count($data) !== 0 ? 'a' : 'b';
    }
}
