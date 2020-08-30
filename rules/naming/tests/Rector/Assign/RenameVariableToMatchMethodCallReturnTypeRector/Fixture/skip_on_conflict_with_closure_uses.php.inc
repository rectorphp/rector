<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

class SkipOnConflictWithClosureUses
{
    public function run()
    {
        $timestamp = time();
        array_map(function ($element) use ($timestamp) {
            $now = new DateTime();
            $time = $now->getTimestamp();
            return $time;
        }, [1, 2, 3]);
    }
}

?>
