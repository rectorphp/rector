<?php

namespace Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class SkipSomeClassMarkTruthyInsideOrAnd
{
    public function run()
    {
        $data = [];
        true || ! count($data);
        ! count($data) || true;
        true && ! count($data);
        ! count($data) && true;
    }
}

?>
