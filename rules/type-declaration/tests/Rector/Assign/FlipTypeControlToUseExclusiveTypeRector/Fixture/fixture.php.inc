<?php

namespace Rector\TypeDeclaration\Tests\Rector\Assign\FlipTypeControlToUseExclusiveTypeRector\Fixture;

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
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\Assign\FlipTypeControlToUseExclusiveTypeRector\Fixture;

use stdClass;

class Fixture
{
    public function run()
    {
        /** @var stdClass|null $stdClass */
        $stdClass = $this->getStdClass();
        if ($stdClass instanceof stdClass) {
            return;
        }
    }
}

?>
