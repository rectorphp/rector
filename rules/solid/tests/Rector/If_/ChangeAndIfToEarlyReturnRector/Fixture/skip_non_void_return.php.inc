<?php

namespace Rector\SOLID\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class SkipNonVoidReturnClass
{
    public function canDrive(Car $car): ?bool
    {
        if ($car->hasWheels && $car->hasFuel) {
            return false;
        }

        return true;
    }
}

?>
