<?php

namespace Rector\CodeQuality\Tests\Rector\Ternary\SwitchNegatedTernaryRector\Fixture;

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
