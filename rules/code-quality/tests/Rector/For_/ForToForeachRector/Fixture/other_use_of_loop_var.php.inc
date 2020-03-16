<?php

namespace Rector\CodeQuality\Tests\Rector\For_\ForToForeachRector\Fixture;

class SkipOtherUseOfLoopVar
{
    public function run($tokens)
    {
        $other = array_reverse($tokens);
        for ($i = 0, $c = count($tokens); $i < $c; ++$i) {
            $value = $tokens[$i][0][1][4];
            return $other[$i];
        }
    }
}
