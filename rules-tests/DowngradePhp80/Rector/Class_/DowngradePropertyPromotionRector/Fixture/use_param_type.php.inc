<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionRector\Fixture;

final class UseParamType
{
    /**
     * @param array<string, int> $values
     */
    public function __construct(
        private array $values
    ) {
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionRector\Fixture;

final class UseParamType
{
    /**
     * @var array<string, int>
     */
    private array $values;
    /**
     * @param array<string, int> $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }
}

?>
