<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class SkipExprCall
{
    public function run()
    {
        $array = [];
        foreach ($this->getVariables() as $property) {
            $array[] = $property;
        }
    }
}

?>
