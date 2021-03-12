<?php

namespace Rector\Tests\EarlyReturn\Rector\Foreach_\ReturnAfterToEarlyOnBreakRector\Fixture;

class SkipMultiBreak
{
    public function run(array $pathConstants)
    {
        foreach ($pathConstants as $allowedPath) {
            if (rand(0, 1)) {
                break;
            }

            if (rand(0,1)) {
                break;
            }
        }
    }
}

?>
