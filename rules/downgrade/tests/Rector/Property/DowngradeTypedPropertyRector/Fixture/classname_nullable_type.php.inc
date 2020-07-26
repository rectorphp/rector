<?php

namespace Rector\Downgrade\Tests\Rector\Property\DowngradeTypedPropertyRector\Fixture;

class ClassNameNullableTypeClass {
    private ?\Rector\Downgrade\Tests\Rector\Property\DowngradeTypedPropertyRector\Source\AnotherClass $property;
}

?>
-----
<?php

namespace Rector\Downgrade\Tests\Rector\Property\DowngradeTypedPropertyRector\Fixture;

class ClassNameNullableTypeClass {
    /**
     * @var \Rector\Downgrade\Tests\Rector\Property\DowngradeTypedPropertyRector\Source\AnotherClass|null
     */
    private $property;
}

?>
