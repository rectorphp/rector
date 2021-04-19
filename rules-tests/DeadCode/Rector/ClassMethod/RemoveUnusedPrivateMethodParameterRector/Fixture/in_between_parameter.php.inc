<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector\Fixture;

final class SkipInBetweenParameter
{
    private $value;
    private $value3;

    private function run($value, $value2, $value3)
    {
        $this->value = $value;
        $this->value3 = $value3;
    }
}
