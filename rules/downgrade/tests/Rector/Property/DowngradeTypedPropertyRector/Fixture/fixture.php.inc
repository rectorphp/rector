<?php

namespace Rector\Downgrade\Tests\Rector\Property\DowngradeTypedPropertyRector\Fixture;

class SomeClass
{
    private string $property;
}

?>
-----
<?php

namespace Rector\Downgrade\Tests\Rector\Property\DowngradeTypedPropertyRector\Fixture;

class SomeClass
{
    /**
     * @var string
     */
    private $property;
}

?>
