<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\FixtureNoDocBlock;

class NullableTypeClass {
    private ?string $property;
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\FixtureNoDocBlock;

class NullableTypeClass {
    private $property;
}

?>
