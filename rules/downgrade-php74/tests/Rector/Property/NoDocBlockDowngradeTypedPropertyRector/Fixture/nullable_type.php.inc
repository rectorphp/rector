<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\NoDocBlockDowngradeTypedPropertyRector\Fixture;

class NullableTypeClass {
    private ?string $property;
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\NoDocBlockDowngradeTypedPropertyRector\Fixture;

class NullableTypeClass {
    private $property;
}

?>
