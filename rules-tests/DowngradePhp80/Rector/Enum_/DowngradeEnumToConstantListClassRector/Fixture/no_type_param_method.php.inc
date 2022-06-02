<?php

namespace Rector\Tests\DowngradePhp80\Rector\Enum_\DowngradeEnumToConstantListClassRector\Fixture;

use Rector\Tests\DowngradePhp80\Rector\Enum_\DowngradeEnumToConstantListClassRector\Source\AnythingYouWant;

final class NoTypeParamMethod
{
    public function with(AnythingYouWant $anythingYouWant)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Enum_\DowngradeEnumToConstantListClassRector\Fixture;

use Rector\Tests\DowngradePhp80\Rector\Enum_\DowngradeEnumToConstantListClassRector\Source\AnythingYouWant;

final class NoTypeParamMethod
{
    /**
     * @param \Rector\Tests\DowngradePhp80\Rector\Enum_\DowngradeEnumToConstantListClassRector\Source\AnythingYouWant::* $anythingYouWant
     */
    public function with($anythingYouWant)
    {
    }
}

?>
