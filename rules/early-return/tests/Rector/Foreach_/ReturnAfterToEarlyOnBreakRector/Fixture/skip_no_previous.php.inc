<?php

namespace Rector\EarlyReturn\Tests\Rector\Foreach_\ReturnAfterToEarlyOnBreakRector\Fixture;

class SkipNoPrevious
{
    public function run(array $pathConstants, string $dirPath, string $allowedPath)
    {
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
