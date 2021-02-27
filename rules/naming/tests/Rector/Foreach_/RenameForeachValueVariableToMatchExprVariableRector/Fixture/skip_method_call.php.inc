<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

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
