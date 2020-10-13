<?php

namespace Rector\SOLID\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class SkipParentIfWithVoidReturn
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
