<?php

namespace Rector\Downgrade\Tests\Rector\Property\DowngradeTypedPropertyRector\Fixture;

class ClassNameClass {
    private \Rector\Downgrade\Tests\Rector\Property\DowngradeTypedPropertyRector\Source\AnotherClass $property;
}

?>
-----
<?php

namespace Rector\Downgrade\Tests\Rector\Property\DowngradeTypedPropertyRector\Fixture;

class ClassNameClass {
    /**
     * @var \Rector\Downgrade\Tests\Rector\Property\DowngradeTypedPropertyRector\Source\AnotherClass
     */
    private $property;
}

?>
