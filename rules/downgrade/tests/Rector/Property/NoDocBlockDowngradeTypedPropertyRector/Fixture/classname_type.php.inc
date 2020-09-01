<?php

namespace Rector\Downgrade\Tests\Rector\Property\NoDocBlockDowngradeTypedPropertyRector\Fixture;

class ClassNameClass {
    private \Rector\Downgrade\Tests\Rector\Property\NoDocBlockDowngradeTypedPropertyRector\Source\AnotherClass $property;
}

?>
-----
<?php

namespace Rector\Downgrade\Tests\Rector\Property\NoDocBlockDowngradeTypedPropertyRector\Fixture;

class ClassNameClass {
    private $property;
}

?>
