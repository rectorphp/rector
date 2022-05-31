<?php

namespace Rector\Tests\DowngradePhp82\Rector\Class_\DowngradeReadonlyClassRector\Fixture;

final readonly class ReadonlyClass
{
    public string $foo;
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp82\Rector\Class_\DowngradeReadonlyClassRector\Fixture;

final class ReadonlyClass
{
    public readonly string $foo;
}

?>
