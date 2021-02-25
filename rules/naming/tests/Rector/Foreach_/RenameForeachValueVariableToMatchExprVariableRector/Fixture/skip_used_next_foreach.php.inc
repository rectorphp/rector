<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class SkipUsedNextForeach
{
    public function run()
    {
        $array = [];
        foreach ($variables as $property) {
            $array[] = $property;
        }

        echo $variable;
    }
}

?>