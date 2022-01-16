<?php

namespace Rector\Tests\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector\Fixture;

class NullableTypeWithDefaultNotNull
{
    protected ?string $test = "test";
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector\Fixture;

class NullableTypeWithDefaultNotNull
{
    /**
     * @var string|null
     */
    protected $test = "test";
}

?>
