<?php

namespace Rector\SOLID\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class OnlyOneIfBeforeClassMethodReturnClass
{
    public function canDrive(Car $car)
    {
        if ($car->hasWheels && $car->hasFuel) {
            $this->canDrive = true;
        }

        if ($car->hasLights && $car->hasEngine) {
            $this->canDrive = false;
        }

        return;
    }
}

?>
-----
<?php

namespace Rector\SOLID\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class OnlyOneIfBeforeClassMethodReturnClass
{
    public function canDrive(Car $car)
    {
        if ($car->hasWheels && $car->hasFuel) {
            $this->canDrive = true;
        }
        if (!$car->hasLights) {
            return;
        }
        if (!$car->hasEngine) {
            return;
        }
        $this->canDrive = false;
    }
}

?>
