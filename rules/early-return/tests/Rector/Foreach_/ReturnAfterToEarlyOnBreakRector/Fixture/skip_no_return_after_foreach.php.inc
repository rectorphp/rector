<?php

namespace Rector\EarlyReturn\Tests\Rector\Foreach_\ReturnAfterToEarlyOnBreakRector\Fixture;

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
