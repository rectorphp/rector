<?php

namespace Rector\CodingStyle\Tests\Rector\Ternary\TernaryConditionVariableAssignmentRector\Fixture;

function skip_different_variable_names($value)
{
    $value ? $a = 1 : $b = 0;
}
