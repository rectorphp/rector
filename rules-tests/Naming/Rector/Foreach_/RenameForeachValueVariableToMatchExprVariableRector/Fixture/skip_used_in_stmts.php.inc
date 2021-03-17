<?php

namespace Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class SkipUsedInStmts
{
    public function run()
    {
        foreach ($variables as $property) {
            $variable[] = $property;
        }
    }
}

?>
