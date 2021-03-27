<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

class SkipUsedArgs
{
    public function run()
    {
        function (Node $node) use ($expr, &$data) {
            $data = rand(0,1);
        };
    }
}