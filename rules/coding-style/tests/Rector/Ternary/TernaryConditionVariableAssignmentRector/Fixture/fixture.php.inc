<?php

namespace Rector\CodingStyle\Tests\Rector\Ternary\TernaryConditionVariableAssignmentRector\Fixture;

function ternary($value)
{
    $value ? $a = 1 : $a = 0;
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Ternary\TernaryConditionVariableAssignmentRector\Fixture;

function ternary($value)
{
    $a = $value ? 1 : 0;
}

?>
