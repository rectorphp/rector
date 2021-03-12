<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\Fixture;

class DocblockExists {
    /**
     * This property is the best one
     */
    private ?string $property;
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\Fixture;

class DocblockExists {
    /**
     * This property is the best one
     * @var string|null
     */
    private $property;
}

?>
