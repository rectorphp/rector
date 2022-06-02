<?php

namespace Rector\Tests\Php80\Rector\Class_\ConstantListClassToEnumRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\ConstantListClassToEnumRector\Source\Gear;

final class ChangeParamType
{
    /**
     * @param Gear::* $gear
     */
    public function changeGear(string $gear)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\ConstantListClassToEnumRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\ConstantListClassToEnumRector\Source\Gear;

final class ChangeParamType
{
    public function changeGear(\Rector\Tests\Php80\Rector\Class_\ConstantListClassToEnumRector\Source\Gear $gear)
    {
    }
}

?>
