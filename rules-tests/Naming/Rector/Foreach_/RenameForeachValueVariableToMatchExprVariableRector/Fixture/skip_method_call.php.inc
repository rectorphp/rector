<?php

namespace Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class SkipMethodCall
{
    public function run()
    {
        $array = [];
        foreach ($this->calls() as $property) {
            $array[] = $property;
        }
    }
}

?>
