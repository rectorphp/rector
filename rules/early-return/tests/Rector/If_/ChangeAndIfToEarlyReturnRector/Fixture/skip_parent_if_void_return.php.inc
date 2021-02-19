<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class SkipParentIfVoidReturn
{
    public function canDrive(Car $car): void
    {
        if ($car->isFerrari) {
            if ($car->hasWheels && $car->hasFuel) {
                $this->canDrive = true;
            }

            return;
        }

        $this->canDrive = false;
    }
}

?>
