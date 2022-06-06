<?php

namespace Rector\Tests\Php81\Rector\Class_\ConstantListClassToEnumRector\Fixture;

use Rector\Tests\Php81\Rector\Class_\ConstantListClassToEnumRector\Source\Gear;

final class EnumTypeProperty
{
    /**
     * @var Gear::* $gear
     */
    private $gear;
}

?>
-----
<?php

namespace Rector\Tests\Php81\Rector\Class_\ConstantListClassToEnumRector\Fixture;

use Rector\Tests\Php81\Rector\Class_\ConstantListClassToEnumRector\Source\Gear;

final class EnumTypeProperty
{
    private \Rector\Tests\Php81\Rector\Class_\ConstantListClassToEnumRector\Source\Gear $gear;
}

?>
