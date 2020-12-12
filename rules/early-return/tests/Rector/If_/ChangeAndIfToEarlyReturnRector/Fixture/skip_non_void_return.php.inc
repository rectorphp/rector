<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class SkipNonVoidReturnClass
{
    public function canDrive(Car $car): ?bool
    {
        if ($car->hasWheels && $car->hasFuel) {
            return false;
        }

        return true;
    }
}

?>
