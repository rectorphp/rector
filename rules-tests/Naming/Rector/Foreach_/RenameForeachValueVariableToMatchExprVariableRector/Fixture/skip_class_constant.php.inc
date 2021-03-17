<?php

namespace Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class SkipClassConstant
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
