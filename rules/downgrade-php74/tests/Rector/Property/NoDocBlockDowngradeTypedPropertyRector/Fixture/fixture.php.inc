<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\NoDocBlockDowngradeTypedPropertyRector\Fixture;

class SomeClass
{
    private string $property;
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\NoDocBlockDowngradeTypedPropertyRector\Fixture;

class SomeClass
{
    private $property;
}

?>
