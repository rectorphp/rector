<?php

namespace Rector\TypeDeclaration\Tests\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\Fixture;

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

namespace Rector\TypeDeclaration\Tests\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\Fixture;

use stdClass;

class Fixture
{
    public function run()
    {
        /** @var stdClass|null $stdClass */
        $stdClass = $this->getStdClass();
        if (!$stdClass instanceof \stdClass) {
            return;
        }

        /** @var null|stdClass $stdClass */
        $stdClass = $this->getStdClass();
        if (!$stdClass instanceof \stdClass) {
            return;
        }
    }
}

?>
