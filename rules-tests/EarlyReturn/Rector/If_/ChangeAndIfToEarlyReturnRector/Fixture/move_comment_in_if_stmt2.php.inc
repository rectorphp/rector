<?php

namespace Rector\Tests\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class MoveCommentInIfStmt2
{
    public function canDrive(Car $car)
    {
        if ($car->hasWheels && $car->hasFuel) {
            // a comment
            return true;
        }

        // another comment
        return false;
    }
}

?>
-----
<?php

namespace Rector\Tests\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class MoveCommentInIfStmt2
{
    public function canDrive(Car $car)
    {
        if (!$car->hasWheels) {
            // another comment
            return false;
        }
        if (!$car->hasFuel) {
            // another comment
            return false;
        }
        // a comment
        return true;
    }
}

?>
