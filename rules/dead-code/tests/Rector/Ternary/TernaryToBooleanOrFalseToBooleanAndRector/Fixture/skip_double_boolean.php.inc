<?php

namespace Rector\DeadCode\Tests\Rector\Ternary\TernaryToBooleanOrFalseToBooleanAndRector\Fixture;

class SkipDoubleBoolean
{
    public function go($values)
    {
        return is_int($values) ? true : false;
    }
}
