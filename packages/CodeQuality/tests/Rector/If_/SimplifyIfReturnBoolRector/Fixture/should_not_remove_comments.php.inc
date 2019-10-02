<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfReturnBoolRector\Fixture;

class ShouldNotRemoveComments
{
    public function isSomething($var)
    {
        if (is_string($var)) {
            return true;
        }

        if (is_array($var)) {
            return true;
        }

        if (is_bool($var)) {
            return true;
        }

        // Let's pretend that this comment is extremely important and meaningful. It should not be removed by Rector.
        return false;
    }
}
