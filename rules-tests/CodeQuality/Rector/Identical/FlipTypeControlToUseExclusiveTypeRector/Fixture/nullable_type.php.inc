<?php

namespace Rector\CodeQuality\Tests\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\Fixture;

use stdClass;

class NullableType
{
    public function run()
    {
        $stdClass = $this->getStdClass();
        if ($stdClass === null) {
            return;
        }
    }

    private function getStdClass(): ?stdClass
    {
        if (rand(0, 1)) {
            return new stdClass;
        }

        return null;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\Fixture;

use stdClass;

class NullableType
{
    public function run()
    {
        $stdClass = $this->getStdClass();
        if (!$stdClass instanceof \stdClass) {
            return;
        }
    }

    private function getStdClass(): ?stdClass
    {
        if (rand(0, 1)) {
            return new stdClass;
        }

        return null;
    }
}

?>
