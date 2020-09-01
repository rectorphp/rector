<?php

namespace Rector\Downgrade\Tests\Rector\Property\NoDocBlockDowngradeTypedPropertyRector\Fixture;

use Rector\Downgrade\Tests\Rector\Property\NoDocBlockDowngradeTypedPropertyRector\Source\AnotherClass;

class ClassNameNullableTypeClass
{
    private ?AnotherClass $property;
}

?>
-----
<?php

namespace Rector\Downgrade\Tests\Rector\Property\NoDocBlockDowngradeTypedPropertyRector\Fixture;

use Rector\Downgrade\Tests\Rector\Property\NoDocBlockDowngradeTypedPropertyRector\Source\AnotherClass;

class ClassNameNullableTypeClass
{
    private $property;
}

?>
