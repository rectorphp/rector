<?php

namespace Rector\EarlyReturn\Tests\Rector\Foreach_\ReturnAfterToEarlyOnBreakRector\Fixture;

class SkipVarUsedMultiple
{
    public function run(array $pathConstants, string $dirPath, string $allowedPath)
    {
        $pathOK = false;
        foreach ($pathConstants as $allowedPath) {
            if ($dirPath == $allowedPath) {
                $pathOK = true;
                break;
            }

            $pathOK = false;
        }

        return $pathOK;
    }
}

?>

