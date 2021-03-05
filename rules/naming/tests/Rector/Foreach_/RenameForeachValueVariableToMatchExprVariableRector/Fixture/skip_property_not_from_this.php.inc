<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class SkipPropertyNotFromThis
{
    public function run()
    {
        $array = [];
        foreach ($object->vars as $property) {
            $array[] = $property;
        }
    }
}

?>
