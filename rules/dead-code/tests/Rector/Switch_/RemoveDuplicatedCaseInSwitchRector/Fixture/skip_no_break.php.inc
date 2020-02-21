<?php

namespace Rector\DeadCode\Tests\Rector\Switch_\RemoveDuplicatedCaseInSwitchRector\Fixture;

class SkipNoBreak
{
    public function run($max)
    {
        $letter = 't';

        switch ($letter) {
            case 't':
                $max *= 1024;
            // no break
            case 'g':
                $max *= 1024;
            // no break
        }

        return $max;
    }
}
