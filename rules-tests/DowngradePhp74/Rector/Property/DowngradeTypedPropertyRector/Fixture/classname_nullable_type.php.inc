<?php

namespace Rector\Tests\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector\Fixture;

use Rector\Tests\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector\Source\AnotherClass;

class ClassNameNullableTypeClass
{
    private ?AnotherClass $property;
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector\Fixture;

use Rector\Tests\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector\Source\AnotherClass;

class ClassNameNullableTypeClass
{
    /**
     * @var \Rector\Tests\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector\Source\AnotherClass|null
     */
    private $property;
}

?>
