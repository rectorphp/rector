<?php

namespace Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class SkipSomeClassMarkTruthyInsideOrAnd
{
    public function run()
    {
        $data = [];
        true || ! count($data);
        true && ! count($data);
    }
}

?>
