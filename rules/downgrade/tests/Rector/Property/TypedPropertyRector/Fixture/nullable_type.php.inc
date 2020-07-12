<?php

namespace Rector\Downgrade\Tests\Rector\Property\TypedPropertyRector\Fixture;

class NullableTypeClass {
    private ?string $property;
}

?>
-----
<?php

namespace Rector\Downgrade\Tests\Rector\Property\TypedPropertyRector\Fixture;

class NullableTypeClass {
    /**
     * @var string|null
     */
    private $property;
}

?>
