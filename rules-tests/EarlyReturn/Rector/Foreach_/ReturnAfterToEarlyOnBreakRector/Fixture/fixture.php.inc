<?php

namespace Rector\EarlyReturn\Tests\Rector\Foreach_\ReturnAfterToEarlyOnBreakRector\Fixture;

class Fixture
{
    public function run(array $pathConstants, string $dirPath, string $allowedPath)
    {
        $pathOK = false;
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
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\Foreach_\ReturnAfterToEarlyOnBreakRector\Fixture;

class Fixture
{
    public function run(array $pathConstants, string $dirPath, string $allowedPath)
    {
        foreach ($pathConstants as $allowedPath) {
            if ($dirPath == $allowedPath) {
                return true;
            }
        }

        return false;
    }
}

?>
