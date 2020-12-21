<?php

namespace Rector\DowngradePhp80\Tests\Rector\Property\DowngradeUnionTypeTypedPropertyRector\Fixture;

class Fixture
{
    public int|string $value;
}

?>
-----
<?php

namespace Rector\DowngradePhp80\Tests\Rector\Property\DowngradeUnionTypeTypedPropertyRector\Fixture;

class Fixture
{
    /**
     * @var int|string
     */
    public $value;
}

?>
