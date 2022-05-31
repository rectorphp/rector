<?php

namespace Rector\Tests\DowngradePhp82\Rector\Class_\DowngradeReadonlyClassRector\Fixture;

final readonly class ReadonlyClassWithPropertyPromotion
{
    public function __construct(private string $foo)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp82\Rector\Class_\DowngradeReadonlyClassRector\Fixture;

final class ReadonlyClassWithPropertyPromotion
{
    public function __construct(private readonly string $foo)
    {
    }
}

?>
