<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

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
