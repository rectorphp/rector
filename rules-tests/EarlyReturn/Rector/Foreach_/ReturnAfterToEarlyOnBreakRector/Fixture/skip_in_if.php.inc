<?php

namespace Rector\Tests\EarlyReturn\Rector\Foreach_\ReturnAfterToEarlyOnBreakRector\Fixture;

class SkipInIf
{
    public function run(array $pathConstants, string $dirPath, string $allowedPath)
    {
        $pathOK = false;
        if (rand(0,1)) {
            foreach ($pathConstants as $allowedPath) {
                if ($dirPath == $allowedPath) {
                    $pathOK = true;
                    break;
                }
            }
        }

        return $pathOK;
    }
}

?>
