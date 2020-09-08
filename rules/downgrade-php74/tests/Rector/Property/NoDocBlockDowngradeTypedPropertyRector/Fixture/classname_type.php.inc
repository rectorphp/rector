<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\NoDocBlockDowngradeTypedPropertyRector\Fixture;

class ClassNameClass {
    private \Rector\DowngradePhp74\Tests\Rector\Property\NoDocBlockDowngradeTypedPropertyRector\Source\AnotherClass $property;
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\NoDocBlockDowngradeTypedPropertyRector\Fixture;

class ClassNameClass {
    private $property;
}

?>
