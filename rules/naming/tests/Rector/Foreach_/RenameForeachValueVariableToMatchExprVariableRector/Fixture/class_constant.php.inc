<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class ClassConstant
{
    private const A_CONSTANTS = [];

    public function run()
    {
        $array = [];
        foreach (self::A_CONSTANTS as $property) {
            $array[] = $property;
        }
    }
}

?>
