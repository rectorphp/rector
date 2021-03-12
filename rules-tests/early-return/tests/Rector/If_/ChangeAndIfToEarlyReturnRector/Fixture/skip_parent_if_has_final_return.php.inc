<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class SkipParentIfHasFinalReturn
{
    public function canDrive()
    {
        if (rand(0, 1)) {
            if (rand(0, 1) && rand(0, 1)) {
                return 1;
            }
        }

        return null;
    }
}

?>
