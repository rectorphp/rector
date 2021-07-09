<?php

namespace Rector\Tests\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionRector\Fixture;

class WithAttributes
{
    public function __construct(
        #[\Deprecated]
        private int $property = 0,
        // normal comment on property promotion should be moved to assign
        private int $property2 = 1,
        // a comment
        // with attribute too
        #[\Deprecated]
        private int $property3 = 2,
        #[\Deprecated] private int $property4 = 3,
        // a comment on one liner attribute
        #[\Deprecated] private int $property5 = 4,
        // normal comment on parameter should be kept
        int $normalParameter
    ) {
    }
}
?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionRector\Fixture;

class WithAttributes
{
    private int $property = 0;
    private int $property2 = 1;
    private int $property3 = 2;
    private int $property4 = 3;
    private int $property5 = 4;
    public function __construct(
        #[\Deprecated]
        int $property = 0,
        int $property2 = 1,
        #[\Deprecated]
        int $property3 = 2,
        #[\Deprecated]
        int $property4 = 3,
        #[\Deprecated]
        int $property5 = 4,
        // normal comment on parameter should be kept
        int $normalParameter
    )
    {
        $this->property = $property;
        // normal comment on property promotion should be moved to assign
        $this->property2 = $property2;
        // a comment
        // with attribute too
        $this->property3 = $property3;
        $this->property4 = $property4;
        // a comment on one liner attribute
        $this->property5 = $property5;
    }
}
?>
