<?php

namespace Rector\Tests\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\Source\stdClass;

class FqcnInNamespace
{
    public function run()
    {
        /** @var stdClass|null $stdClass */
        $stdClass = $this->getStdClass();
        if ($stdClass === null) {
            return;
        }
    }

    public function getStdClass()
    {
        return new \stdClass();
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\Source\stdClass;

class FqcnInNamespace
{
    public function run()
    {
        $stdClass = $this->getStdClass();
        if (!$stdClass instanceof \Rector\Tests\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\Source\stdClass) {
            return;
        }
    }

    public function getStdClass()
    {
        return new \stdClass();
    }
}

?>
