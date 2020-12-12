<?php

namespace Rector\DowngradePhp80\Tests\Rector\Property\DowngradeUnionTypeTypedPropertyRector\Fixture;

class SomeClass
{
    public int|string $value;
}

?>
-----
<?php

namespace Rector\DowngradePhp80\Tests\Rector\Property\DowngradeUnionTypeTypedPropertyRector\Fixture;

class SomeClass
{
    /**
     * @var int|string
     */
    public $value;
}

?>
