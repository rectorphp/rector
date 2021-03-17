<?php

namespace Rector\Tests\CodeQuality\Rector\For_\ForToForeachRector\Fixture;

class SkipUnset
{
    public function run($tokens)
    {
        for ($i = 0, $c = count($tokens); $i < $c; $i++) {
            unset($tokens[$i]);
        }
    }
}

?>
