<?php

namespace Rector\Downgrade\Tests\Rector\Property\TypedPropertyRector\Fixture;

class SomeClass {
    private string $property;
}

?>
-----
<?php

namespace Rector\Downgrade\Tests\Rector\Property\TypedPropertyRector\Fixture;

class SomeClass {
    /**
     * @var string
     */
    private $property;
}

?>
