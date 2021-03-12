<?php

namespace Rector\Tests\EarlyReturn\Rector\Foreach_\ReturnAfterToEarlyOnBreakRector\Fixture;

class SkipPrevAssignNotLinearExpression
{
    public function run(array $pathConstants, string $dirPath, string $allowedPath)
    {
        if (rand(0, 1)) {
            $pathOK = false;
        }

        foreach ($pathConstants as $allowedPath) {
            if ($dirPath == $allowedPath) {
                $pathOK = true;
                break;
            }
        }

        return $pathOK;
    }
}

?>
