<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class BooleanNot
{
    public function canDrive(Car $car)
    {
        if (!$car->hasWheels && !$car->hasFuel) {
            $this->canDrive = true;
        }

        return;
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class BooleanNot
{
    public function canDrive(Car $car)
    {
        if ($car->hasWheels) {
            return;
        }
        if ($car->hasFuel) {
            return;
        }
        $this->canDrive = true;
    }
}

?>
