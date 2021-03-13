<?php

namespace Rector\Tests\EarlyReturn\Rector\Foreach_\ReturnAfterToEarlyOnBreakRector\Fixture;

class SkipNoBreak
{
    public function run(array $pathConstants)
    {
        foreach ($pathConstants as $allowedPath) {
        }
    }
}

?>
