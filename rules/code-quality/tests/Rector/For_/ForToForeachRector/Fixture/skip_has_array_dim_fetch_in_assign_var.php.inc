<?php

namespace Rector\CodeQuality\Tests\Rector\For_\ForToForeachRector\Fixture;

class SkipHasArrayDimFetchInAssignVar
{
    public function run($tokens)
    {
        for ($i = 0, $c = count($tokens); $i < $c; $i++) {
            $tokens[$i] = [];
        }
    }
}

?>
