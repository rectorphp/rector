<?php

declare(strict_types=1);

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedAssignVariableRector\Fixture;

class SkipMultiAssign
{
    public function run()
    {
        $values = $peValId = $peColId = $auto = 0;

        if ($values) {
            return true;
        }

        return false;
    }
}
