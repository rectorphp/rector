<?php

namespace Rector\Tests\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector\Fixture;

final class NullableTypeWithDefault
{
    private ?string $property = null;
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector\Fixture;

final class NullableTypeWithDefault
{
    /**
     * @var string|null
     */
    private $property;
}

?>
