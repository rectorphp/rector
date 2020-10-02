<?php

namespace Rector\SOLID\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class SkipNonFirstLevelIf
{
    public function canDrive(Car $car)
    {
        if ($car->wheelsCount() > 2) {
            if ($car->hasWheels && $car->hasFuel) {
                $this->canDrive = true;
            }

            return;
        }

        $this->canDrive = true;
    }
}

?>
