<?php

namespace Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class SomeClassMarkTruthyCheckInWhile
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