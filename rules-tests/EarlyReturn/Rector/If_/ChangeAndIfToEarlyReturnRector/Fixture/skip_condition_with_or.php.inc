<?php

namespace Rector\Tests\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class SkipConditionWithOr
{
    public function canDrive(Car $car)
    {
        if ($car->hasWheels || $car->hasFuel) {
            $this->canDrive = false;
        }

        return;
    }
}

?>
