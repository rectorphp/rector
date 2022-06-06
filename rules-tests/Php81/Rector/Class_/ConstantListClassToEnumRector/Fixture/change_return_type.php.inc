<?php

namespace Rector\Tests\Php81\Rector\Class_\ConstantListClassToEnumRector\Fixture;

use Rector\Tests\Php81\Rector\Class_\ConstantListClassToEnumRector\Source\Gear;

final class ChangeReturnType
{
    /**
     * @return Gear::* $gear
     */
    public function changeGear(): string
    {
        return Gear::FIRST;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php81\Rector\Class_\ConstantListClassToEnumRector\Fixture;

use Rector\Tests\Php81\Rector\Class_\ConstantListClassToEnumRector\Source\Gear;

final class ChangeReturnType
{
    public function changeGear(): \Rector\Tests\Php81\Rector\Class_\ConstantListClassToEnumRector\Source\Gear
    {
        return Gear::FIRST;
    }
}

?>
