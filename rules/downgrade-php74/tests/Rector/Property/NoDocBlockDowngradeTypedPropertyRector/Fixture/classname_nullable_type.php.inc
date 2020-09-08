<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\NoDocBlockDowngradeTypedPropertyRector\Fixture;

use Rector\DowngradePhp74\Tests\Rector\Property\NoDocBlockDowngradeTypedPropertyRector\Source\AnotherClass;

class ClassNameNullableTypeClass
{
    private ?AnotherClass $property;
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\NoDocBlockDowngradeTypedPropertyRector\Fixture;

use Rector\DowngradePhp74\Tests\Rector\Property\NoDocBlockDowngradeTypedPropertyRector\Source\AnotherClass;

class ClassNameNullableTypeClass
{
    private $property;
}

?>
