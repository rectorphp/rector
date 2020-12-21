<?php

namespace Rector\DowngradePhp80\Tests\Rector\Class_\DowngradePropertyPromotionToConstructorPropertyAssignRector\Fixture;

class Fixture
{
    public function __construct(public float $value = 0.0)
    {
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp80\Tests\Rector\Class_\DowngradePropertyPromotionToConstructorPropertyAssignRector\Fixture;

class Fixture
{
    public float $value;
    public function __construct(float $value = 0.0)
    {
        $this->value = $value;
    }
}

?>
