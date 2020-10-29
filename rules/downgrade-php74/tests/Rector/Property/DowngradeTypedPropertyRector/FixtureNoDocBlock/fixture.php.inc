<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\FixtureNoDocBlock;

class SomeClass
{
    private string $property;
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\FixtureNoDocBlock;

class SomeClass
{
    private $property;
}

?>
