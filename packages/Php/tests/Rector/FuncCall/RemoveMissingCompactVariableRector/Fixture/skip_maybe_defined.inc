<?php

namespace Rector\Php\Tests\Rector\FuncCall\RemoveMissingCompactVariableRector\Fixture;

class SkipMaybeDefined
{
    public function run($cond)
    {
        $value = 'yes';
        if ($cond) {
            $maybeExisting = true;
        }

        compact('value', 'maybeExisting');
    }
}
