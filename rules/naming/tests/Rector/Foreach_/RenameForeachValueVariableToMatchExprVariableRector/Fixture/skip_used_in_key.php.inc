<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class SkipUsedInKey
{
    public function run()
    {
        $array = [];
        foreach ($variables as $variable => $property) {
            $array[$variable] = $property;
        }
    }
}

?>
