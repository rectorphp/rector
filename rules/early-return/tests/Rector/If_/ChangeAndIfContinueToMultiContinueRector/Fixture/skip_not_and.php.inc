<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeAndIfContinueToMultiContinueRector\Fixture;

class SkipNotAnd
{
    public function canDrive(Car $newCar)
    {
        foreach ($cars as $car) {
            if ($car->hasWheels || $car->hasFuel) {
                continue;
            }

            $car->setWheel($newCar->wheel);
            $car->setFuel($newCar->fuel);
        }
    }
}

?>
