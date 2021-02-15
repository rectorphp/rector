<?php

namespace Rector\EarlyReturn\Tests\Rector\Foreach_\ReturnAfterToEarlyOnBreakRector\Fixture;

class SkipAssignVarExpr
{
    private $pathOK = false;

    public function run(array $pathConstants, string $dirPath, string $allowedPath, bool $pathOK = null)
    {
        $pathOK || $this->pathOK = true;
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
