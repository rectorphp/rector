<?php

namespace Rector\Tests\EarlyReturn\Rector\Foreach_\ReturnAfterToEarlyOnBreakRector\Fixture;

class SkipBeforeBreakNotAssign
{
    public function run(array $pathConstants, string $dirPath, string $allowedPath)
    {
        $pathOK = false;
        foreach ($pathConstants as $allowedPath) {
            if ($dirPath == $allowedPath) {
                execute();
                break;
            }
        }

        return $pathOK;
    }
}

?>
