<?php


namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;


class SkipWithElseElseIf
{
    public function canDrive(Car $car): void
    {
        if ($car->hasWheels && $car->hasFuel) {
            $this->canDrive = true;
        } else {
            return;
        }

        return;
    }

    public function hasWheels(Car $car): void
    {
        if ($car->hasWheels && $car->hasFuel) {
            $this->canDrive = true;
        } elseif ($car->hasFuel) {
            return;
        }

        return;
    }
}
