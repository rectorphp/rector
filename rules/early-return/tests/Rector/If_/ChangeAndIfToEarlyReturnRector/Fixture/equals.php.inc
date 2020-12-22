<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class Equals
{
    public function canDrive(Car $car)
    {
        if ($car->hasWheels === true && $car->hasFuel === true) {
            $this->canDrive = true;
        }

        return;
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class Equals
{
    public function canDrive(Car $car)
    {
        if ($car->hasWheels !== true) {
            return;
        }
        if ($car->hasFuel !== true) {
            return;
        }
        $this->canDrive = true;
    }
}

?>
