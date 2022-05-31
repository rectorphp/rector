<?php

namespace Rector\Tests\DowngradePhp82\Rector\Class_\DowngradeReadonlyClassRector\Fixture;

final readonly class StaticProperty
{
    public static string $foo;
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp82\Rector\Class_\DowngradeReadonlyClassRector\Fixture;

final class StaticProperty
{
    public static string $foo;
}

?>
