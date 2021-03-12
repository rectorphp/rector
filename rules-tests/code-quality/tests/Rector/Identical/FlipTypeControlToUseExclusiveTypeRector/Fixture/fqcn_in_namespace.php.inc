<?php

namespace Rector\CodeQuality\Tests\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\Fixture;

use Rector\CodeQuality\Tests\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\Source\stdClass;

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
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\Fixture;

use Rector\CodeQuality\Tests\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\Source\stdClass;

class FqcnInNamespace
{
    public function run()
    {
        $stdClass = $this->getStdClass();
        if (!$stdClass instanceof \Rector\CodeQuality\Tests\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\Source\stdClass) {
            return;
        }
    }
}

?>
