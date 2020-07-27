<?php

namespace Rector\Downgrade\Tests\Rector\Property\DowngradeTypedPropertyRector\Fixture;

class DocBlockExists {
    /**
     * This property is the best one
     */
    private ?string $property;
}

?>
-----
<?php

namespace Rector\Downgrade\Tests\Rector\Property\DowngradeTypedPropertyRector\Fixture;

class DocBlockExists {
    /**
     * This property is the best one
     * @var string|null
     */
    private $property;
}

?>
