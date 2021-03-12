<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeOrIfContinueToMultiContinueRector\Fixture;

class HasComment
{
    public function canDrive(Car $newCar)
    {
        foreach ($cars as $car) {
            // a comment
            if ($car->hasWheels() || $car->hasFuel()) {
                continue;
            }
            $car->setWheel($newCar->wheel);
            $car->setFuel($newCar->fuel);
        }
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeOrIfContinueToMultiContinueRector\Fixture;

class HasComment
{
    public function canDrive(Car $newCar)
    {
        foreach ($cars as $car) {
            // a comment
            if ($car->hasWheels()) {
                continue;
            }
            if ($car->hasFuel()) {
                continue;
            }
            $car->setWheel($newCar->wheel);
            $car->setFuel($newCar->fuel);
        }
    }
}

?>
