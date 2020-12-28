<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\Fixture;

class ClassnameType {
    private \Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\Source\AnotherClass $property;
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\Fixture;

class ClassnameType {
    /**
     * @var \Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\Source\AnotherClass
     */
    private $property;
}

?>
