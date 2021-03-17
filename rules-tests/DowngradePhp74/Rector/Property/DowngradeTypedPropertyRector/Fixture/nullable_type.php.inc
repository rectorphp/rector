<?php

namespace Rector\Tests\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector\Fixture;

class NullableType {
    private ?string $property;
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector\Fixture;

class NullableType {
    /**
     * @var string|null
     */
    private $property;
}

?>
