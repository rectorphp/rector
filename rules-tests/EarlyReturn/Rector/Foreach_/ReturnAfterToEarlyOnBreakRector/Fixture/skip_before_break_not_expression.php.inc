<?php

namespace Rector\Tests\EarlyReturn\Rector\Foreach_\ReturnAfterToEarlyOnBreakRector\Fixture;

class SkipBeforeBreakNotExpression
{
    public function run(array $pathConstants, string $dirPath, string $allowedPath)
    {
        $pathOK = false;
        foreach ($pathConstants as $allowedPath) {
            if ($dirPath == $allowedPath) {
                if (rand(0, 1)) {
                    $pathOK = true;
                    continue;
                }
                break;
            }
        }

        return $pathOK;
    }
}

?>
