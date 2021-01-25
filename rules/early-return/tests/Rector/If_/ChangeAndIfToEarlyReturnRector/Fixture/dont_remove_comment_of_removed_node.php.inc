<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class DontRemoveCommentOfRemovedNode
{
    public function canDrive(Car $car): void
    {
        // keep this comment
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

class DontRemoveCommentOfRemovedNode
{
    public function canDrive(Car $car): void
    {
        // keep this comment
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
