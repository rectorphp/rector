<?php

namespace Rector\Tests\DowngradePhp82\Rector\Class_\DowngradeReadonlyClassRector\Fixture;

final readonly class NotPropertyPromotion
{
    public function __construct(string $bar)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp82\Rector\Class_\DowngradeReadonlyClassRector\Fixture;

final class NotPropertyPromotion
{
    public function __construct(string $bar)
    {
    }
}

?>
