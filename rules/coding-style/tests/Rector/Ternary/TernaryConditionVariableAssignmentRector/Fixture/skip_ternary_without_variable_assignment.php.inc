<?php

namespace Rector\CodingStyle\Tests\Rector\Ternary\TernaryConditionVariableAssignmentRector\Fixture;

function skip_ternary_without_variable_assignment($value)
{
    $value ? 1 : $a = 0;
}
