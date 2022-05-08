<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector\Fixture;

class SkipEmptyArgs
{
    public function run()
    {
        array_push();

        echo 'test';
    }
}