<?php

namespace Rector\Tests\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionRector\Fixture;

final class AddArrayDefaults
{
    public function __construct(
        protected array $values = [])
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionRector\Fixture;

final class AddArrayDefaults
{
    protected array $values = [];
    public function __construct(array $values = [])
    {
        $this->values = $values;
    }
}

?>
