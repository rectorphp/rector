<?php

namespace Rector\Tests\DowngradePhp82\Rector\Class_\DowngradeReadonlyClassRector\Fixture;

final readonly class CombinePropertyAndPropertyPromotion
{
    private string $foo;

    public function __construct(private string $bar)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp82\Rector\Class_\DowngradeReadonlyClassRector\Fixture;

final class CombinePropertyAndPropertyPromotion
{
    private readonly string $foo;

    public function __construct(private readonly string $bar)
    {
    }
}

?>
