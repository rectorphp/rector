<?php

namespace Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class SkipEmptySingularValue
{
    public function run()
    {
        foreach ($_FOO as $file) {

        }
    }
}
