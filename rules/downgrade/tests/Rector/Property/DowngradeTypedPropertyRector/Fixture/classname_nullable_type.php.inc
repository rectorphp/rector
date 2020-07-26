<?php

namespace Rector\Downgrade\Tests\Rector\Property\DowngradeTypedPropertyRector\Fixture;

use Rector\Downgrade\Tests\Rector\Property\DowngradeTypedPropertyRector\Source\AnotherClass;

class ClassNameNullableTypeClass
{
    private ?AnotherClass $property;
}

?>
-----
<?php

namespace Rector\Downgrade\Tests\Rector\Property\DowngradeTypedPropertyRector\Fixture;

use Rector\Downgrade\Tests\Rector\Property\DowngradeTypedPropertyRector\Source\AnotherClass;

class ClassNameNullableTypeClass
{
    /**
     * @var \Rector\Downgrade\Tests\Rector\Property\DowngradeTypedPropertyRector\Source\AnotherClass|null
     */
    private $property;
}

?>
