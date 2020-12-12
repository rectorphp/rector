<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class SkipWithVoidReturn
{
    public function canDrive(Car $car): void
    {
        if ($car->isFerrari) {
            if ($car->hasWheels && $car->hasFuel) {
                return;
            }
        }

        $this->canDrive = true;
    }
}

?>
