<?php

namespace Rector\Tests\EarlyReturn\Rector\Foreach_\ReturnAfterToEarlyOnBreakRector\Fixture;

class SkipNoReturnAfterForeach
{
    public function run(array $pathConstants, string $dirPath, string $allowedPath)
    {
        foreach ($pathConstants as $allowedPath) {
            if ($dirPath == $allowedPath) {
                execute();
                break;
            }
        }
    }
}

?>
