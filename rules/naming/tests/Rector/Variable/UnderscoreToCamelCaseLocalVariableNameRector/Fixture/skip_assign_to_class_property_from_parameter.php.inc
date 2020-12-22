<?php

namespace Rector\Naming\Tests\Rector\Variable\UnderscoreToCamelCaseLocalVariableNameRector\Fixture;

final class SkipAssignToClassPropertyFromParameter
{
    public function run($a_b)
    {
        $this->a_b = $a_b;
    }
}

?>
