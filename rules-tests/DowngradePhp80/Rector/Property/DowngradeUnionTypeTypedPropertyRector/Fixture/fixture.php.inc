<?php

namespace Rector\Tests\DowngradePhp80\Rector\Property\DowngradeUnionTypeTypedPropertyRector\Fixture;

class Fixture
{
    public int|string $value;
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Property\DowngradeUnionTypeTypedPropertyRector\Fixture;

class Fixture
{
    /**
     * @var int|string
     */
    public $value;
}

?>
