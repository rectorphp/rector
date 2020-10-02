<?php

namespace Rector\SOLID\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class EqualsClass
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

namespace Rector\SOLID\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class EqualsClass
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
