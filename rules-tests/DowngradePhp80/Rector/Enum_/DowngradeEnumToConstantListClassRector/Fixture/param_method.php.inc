<?php

namespace Rector\Tests\DowngradePhp80\Rector\Enum_\DowngradeEnumToConstantListClassRector\Fixture;

use Rector\Tests\DowngradePhp80\Rector\Enum_\DowngradeEnumToConstantListClassRector\Source\GearValue;

final class ParamMethod
{
    public function changeGear(GearValue $gearValue)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Enum_\DowngradeEnumToConstantListClassRector\Fixture;

use Rector\Tests\DowngradePhp80\Rector\Enum_\DowngradeEnumToConstantListClassRector\Source\GearValue;

final class ParamMethod
{
    /**
     * @param \Rector\Tests\DowngradePhp80\Rector\Enum_\DowngradeEnumToConstantListClassRector\Source\GearValue::* $gearValue
     */
    public function changeGear(string $gearValue)
    {
    }
}

?>
