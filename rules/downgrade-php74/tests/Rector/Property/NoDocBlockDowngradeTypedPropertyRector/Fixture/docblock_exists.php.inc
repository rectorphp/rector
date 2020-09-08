<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\NoDocBlockDowngradeTypedPropertyRector\Fixture;

class DocBlockExists {
    /**
     * This property is the best one
     */
    private ?string $property;
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\NoDocBlockDowngradeTypedPropertyRector\Fixture;

class DocBlockExists {
    /**
     * This property is the best one
     */
    private $property;
}

?>
