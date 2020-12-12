<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\Fixture;

use Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\Source\AnotherClass;

class ClassNameNullableTypeClass
{
    private ?AnotherClass $property;
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\Fixture;

use Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\Source\AnotherClass;

class ClassNameNullableTypeClass
{
    /**
     * @var \Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\Source\AnotherClass|null
     */
    private $property;
}

?>
