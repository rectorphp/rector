<?php

namespace Rector\DowngradePhp80\Tests\Rector\Class_\DowngradePropertyPromotionToConstructorPropertyAssignRector\Fixture;

class SomeClass
{
    public function __construct(public float $value = 0.0)
    {
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp80\Tests\Rector\Class_\DowngradePropertyPromotionToConstructorPropertyAssignRector\Fixture;

class SomeClass
{
    public float $value;
    public function __construct(float $value = 0.0)
    {
        $this->value = $value;
    }
}

?>
