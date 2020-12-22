<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class Fixture
{
    public function canDrive(Car $car): void
    {
        if ($car->hasWheels && $car->hasFuel) {
            $this->canDrive = true;
        }

        return;
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class Fixture
{
    public function canDrive(Car $car): void
    {
        if (!$car->hasWheels) {
            return;
        }
        if (!$car->hasFuel) {
            return;
        }
        $this->canDrive = true;
    }
}

?>
