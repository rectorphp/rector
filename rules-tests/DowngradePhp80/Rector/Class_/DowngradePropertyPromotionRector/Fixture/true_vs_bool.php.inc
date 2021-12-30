<?php

namespace Rector\Tests\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionRector\Fixture;

class TrueVsBool
{
    /** @param array<string, true> $value */
    public function __construct(private array $value)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionRector\Fixture;

class TrueVsBool
{
    /**
     * @var array<string, true>
     */
    private array $value;
    /** @param array<string, true> $value */
    public function __construct(array $value)
    {
        $this->value = $value;
    }
}

?>
