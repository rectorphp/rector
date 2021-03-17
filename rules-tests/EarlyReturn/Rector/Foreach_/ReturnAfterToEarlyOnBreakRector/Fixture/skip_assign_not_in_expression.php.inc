<?php

namespace Rector\Tests\EarlyReturn\Rector\Foreach_\ReturnAfterToEarlyOnBreakRector\Fixture;

class SkipAssignNotInExpression
{
    private $pathOK = false;

    public function run(array $pathConstants, string $dirPath, string $allowedPath, bool $pathOK = null)
    {
        $pathOK !== null || $this->pathOK = true;
        foreach ($pathConstants as $allowedPath) {
            if ($dirPath == $allowedPath) {
                $this->pathOK = true;
                break;
            }
        }

        return $this->pathOK;
    }
}

?>
