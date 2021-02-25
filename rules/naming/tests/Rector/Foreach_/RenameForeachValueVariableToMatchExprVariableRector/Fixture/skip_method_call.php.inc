<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class SkipMethodCall
{
    private const A_CONSTANTS = [];

    public function run()
    {
        $array = [];
        foreach ($this->calls() as $property) {
            $array[] = $property;
        }
    }
}

?>
