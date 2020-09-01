<?php

namespace Rector\Downgrade\Tests\Rector\Property\NoDocBlockDowngradeTypedPropertyRector\Fixture;

class NullableTypeClass {
    private ?string $property;
}

?>
-----
<?php

namespace Rector\Downgrade\Tests\Rector\Property\NoDocBlockDowngradeTypedPropertyRector\Fixture;

class NullableTypeClass {
    private $property;
}

?>
