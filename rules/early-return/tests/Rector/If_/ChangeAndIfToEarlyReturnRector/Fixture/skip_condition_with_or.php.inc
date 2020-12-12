<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

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
