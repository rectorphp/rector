<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\Fixture;

class ClassNameClass {
    private \Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\Source\AnotherClass $property;
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\Fixture;

class ClassNameClass {
    /**
     * @var \Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\Source\AnotherClass
     */
    private $property;
}

?>
