<?php

namespace Rector\Downgrade\Tests\Rector\Property\DowngradeTypedPropertyRector\Fixture;

class NullableTypeClass {
    private ?string $property;
}

?>
-----
<?php

namespace Rector\Downgrade\Tests\Rector\Property\DowngradeTypedPropertyRector\Fixture;

class NullableTypeClass {
    /**
     * @var string|null
     */
    private $property;
}

?>
