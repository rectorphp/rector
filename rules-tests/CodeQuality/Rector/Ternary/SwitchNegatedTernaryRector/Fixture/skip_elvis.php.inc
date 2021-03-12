<?php

namespace Rector\Tests\CodeQuality\Rector\Ternary\SwitchNegatedTernaryRector\Fixture;

class SkipElvis
{
    public function run(bool $upper, string $name)
    {
        return ! $upper ?: strtoupper($name);
    }
}

?>
