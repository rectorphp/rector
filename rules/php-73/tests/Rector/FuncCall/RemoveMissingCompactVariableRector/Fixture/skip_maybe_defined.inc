<?php

namespace Rector\Php73\Tests\Rector\FuncCall\RemoveMissingCompactVariableRector\Fixture;

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
