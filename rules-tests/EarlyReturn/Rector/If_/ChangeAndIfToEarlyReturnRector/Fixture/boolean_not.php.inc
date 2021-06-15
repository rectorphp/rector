<?php

namespace Rector\Tests\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

final class SomeBooleanNot
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

namespace Rector\Tests\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

final class SomeBooleanNot
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
