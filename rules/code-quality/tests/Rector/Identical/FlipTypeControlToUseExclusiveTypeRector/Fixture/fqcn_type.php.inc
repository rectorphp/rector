<?php

namespace Rector\CodeQuality\Tests\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\Fixture;

class FqcnType
{
    public function run()
    {
        /** @var \stdClass|null $stdClass */
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

class FqcnType
{
    public function run()
    {
        $stdClass = $this->getStdClass();
        if (!$stdClass instanceof \stdClass) {
            return;
        }
    }
}

?>
