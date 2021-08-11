<?php

namespace Rector\Tests\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class MoveCommentInIfStmt
{
    public function canDrive(Car $car)
    {
        if ($car->hasWheels && $car->hasFuel) {
            // a comment
            return true;
        }

        return false;
    }
}

?>
-----
<?php

namespace Rector\Tests\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class MoveCommentInIfStmt
{
    public function canDrive(Car $car)
    {
        if (!$car->hasWheels) {
            return false;
        }
        if (!$car->hasFuel) {
            return false;
        }
        // a comment
        return true;
    }
}

?>
