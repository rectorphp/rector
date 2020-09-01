<?php

namespace Rector\Downgrade\Tests\Rector\Property\NoDocBlockDowngradeTypedPropertyRector\Fixture;

class SomeClass
{
    private string $property;
}

?>
-----
<?php

namespace Rector\Downgrade\Tests\Rector\Property\NoDocBlockDowngradeTypedPropertyRector\Fixture;

class SomeClass
{
    private $property;
}

?>
