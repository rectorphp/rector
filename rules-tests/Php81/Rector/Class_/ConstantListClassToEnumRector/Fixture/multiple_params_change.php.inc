<?php

namespace Rector\Tests\Php81\Rector\Class_\ConstantListClassToEnumRector\Fixture;

use Rector\Tests\Php81\Rector\Class_\ConstantListClassToEnumRector\Source\Gear;

final class MultipleParamsChange
{
    /**
     * @param string $carType
     * @param Gear::* $gear
     * @param int $speed
     */
    public function changeGear($carType, string $gear, $speed)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Php81\Rector\Class_\ConstantListClassToEnumRector\Fixture;

use Rector\Tests\Php81\Rector\Class_\ConstantListClassToEnumRector\Source\Gear;

final class MultipleParamsChange
{
    /**
     * @param string $carType
     * @param int $speed
     */
    public function changeGear($carType, \Rector\Tests\Php81\Rector\Class_\ConstantListClassToEnumRector\Source\Gear $gear, $speed)
    {
    }
}

?>
