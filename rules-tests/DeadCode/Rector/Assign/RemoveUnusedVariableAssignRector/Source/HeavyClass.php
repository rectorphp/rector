<?php

declare(strict_types=1);

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Source;

use RuntimeException;

class HeavyClass
{
    public function __construct()
    {
        if (rand(0, 1)) {
            throw new RuntimeException();
        }
    }
}
