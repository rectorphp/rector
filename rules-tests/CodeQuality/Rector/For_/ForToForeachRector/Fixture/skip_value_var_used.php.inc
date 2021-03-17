<?php

namespace Rector\Tests\CodeQuality\Rector\For_\ForToForeachRector\Fixture;

class SkipValueVarUsed
{
    public function run($tokens, $token)
    {
        for ($i = 0; $i < count($tokens); $i++) {
        }
        echo $token;
    }
}

?>
