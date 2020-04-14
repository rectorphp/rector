<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveUnusedAssignVariableRector\Fixture;

class SkipFor
{
    public function run($nesting = 100)
    {
        for ($j = 2; $j < $nesting; $j++) {
        }
    }
}
