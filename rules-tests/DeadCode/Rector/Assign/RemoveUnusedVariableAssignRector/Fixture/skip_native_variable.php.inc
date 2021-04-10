<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

class SkipNativeVariable
{
    public function cleanFiles()
    {
        $_FILES = [];
    }
}