<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeOrIfContinueToMultiContinueRector\Fixture;

class Identical
{
    public function canDrive(Car $newCar)
    {
        foreach ($cars as $car) {
            if ($car->getWheel() === 4 || $car->getFuel() === 'full') {
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

class Identical
{
    public function canDrive(Car $newCar)
    {
        foreach ($cars as $car) {
            if ($car->getWheel() === 4) {
                continue;
            }
            if ($car->getFuel() === 'full') {
                continue;
            }
            $car->setWheel($newCar->wheel);
            $car->setFuel($newCar->fuel);
        }
    }
}

?>
