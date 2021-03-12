<?php

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveUnusedAssignVariableRector\Fixture;

final class SkipVarOverrideAndReUse
{
    public function run()
    {
        $sep = '';
        while ($row = \pg_fetch_assoc($query)) {
            $text .= "{$sep}" . ($row['b'] ?: $row['a']);
            $sep = ', ';
        }
    }
}
