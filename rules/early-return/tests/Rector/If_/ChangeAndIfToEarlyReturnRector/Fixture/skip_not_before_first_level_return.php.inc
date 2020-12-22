<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class SkipNotBeforeFirstLevelReturn
{
    public function canDrive(Car $car)
    {
        if ($car->hasWheels && $car->hasFuel) {
            $this->canDrive = true;
        }

        if (!$car->hasLights || !$car->hasEngine) {
            $this->canDrive = false;
        }

        return;
    }
}

?>
