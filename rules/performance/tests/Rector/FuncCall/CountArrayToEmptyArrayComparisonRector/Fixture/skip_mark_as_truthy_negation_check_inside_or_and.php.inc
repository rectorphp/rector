<?php

namespace Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class SomeClassMarkTruthyInsideOrAnd
{
    public function run()
    {
        $data = [];
        true || ! count($data);
        true && ! count($data);
    }
}

?>
