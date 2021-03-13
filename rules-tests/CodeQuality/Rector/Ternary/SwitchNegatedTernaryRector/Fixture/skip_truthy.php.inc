<?php

namespace Rector\Tests\CodeQuality\Rector\Ternary\SwitchNegatedTernaryRector\Fixture;

class SkipTruthy
{
    public function run(bool $upper, string $name)
    {
        return $upper
            ? strtoupper($name)
            : $name;
    }
}

?>
