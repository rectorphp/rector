<?php

namespace Rector\CodeQuality\Tests\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\Fixture;

use stdClass;

class Fixture
{
    public function run()
    {
        /** @var stdClass|null $stdClass */
        $stdClass = $this->getStdClass();
        if ($stdClass === null) {
            return;
        }

        /** @var null|stdClass $stdClass */
        $stdClass = $this->getStdClass();
        if ($stdClass === null) {
            return;
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\Fixture;

use stdClass;

class Fixture
{
    public function run()
    {
        $stdClass = $this->getStdClass();
        if (!$stdClass instanceof \stdClass) {
            return;
        }

        $stdClass = $this->getStdClass();
        if (!$stdClass instanceof \stdClass) {
            return;
        }
    }
}

?>
