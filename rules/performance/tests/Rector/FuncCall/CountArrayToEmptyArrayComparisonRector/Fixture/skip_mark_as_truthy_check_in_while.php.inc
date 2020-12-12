<?php

namespace Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

use Countable;

class SkipMarkTruthyCheckInWhile
{
    public function run(Countable $c)
    {
        $data = [];
        while (count($data)) {
            $data = $c;
        }
    }
}

?>