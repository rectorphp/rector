<?php

namespace Rector\Tests\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector\Fixture;

class ClassnameType {
    private \Rector\Tests\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector\Source\AnotherClass $property;
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector\Fixture;

class ClassnameType {
    /**
     * @var \Rector\Tests\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector\Source\AnotherClass
     */
    private $property;
}

?>
