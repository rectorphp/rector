<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

class SkipNextIncludeAnotherFile
{
    public function run()
    {
        $variable = 'test';

        if (rand(0, 1)) {
            include 'test.php';
        }

        if (rand(0, 1)) {
            include_once 'test.php';
        }

        if (rand(0, 1)) {
            require 'test.php';
        }

        if (rand(0, 1)) {
            require_once 'test.php';
        }
    }
}