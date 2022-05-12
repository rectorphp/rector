<?php

namespace Rector\Tests\Php82\Rector\Class_\ReadOnlyClassRector\Fixture;

final class OnlyReadonlyProperty
{
   private readonly string $property;
}

?>
-----
<?php

namespace Rector\Tests\Php82\Rector\Class_\ReadOnlyClassRector\Fixture;

final readonly class OnlyReadonlyProperty
{
   private string $property;
}

?>
