<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector\Fixture;

class StrictPapa
{
    public function getIt($value, $value2)
    {
        $combo = $value + $value2;
    }

}

class SkipParentRequired extends StrictPapa
{
    public function getIt($value, $value2)
    {
        $this->value = $value;
    }
}
