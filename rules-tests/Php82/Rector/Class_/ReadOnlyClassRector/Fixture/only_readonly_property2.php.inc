<?php

namespace Rector\Tests\Php82\Rector\Class_\ReadOnlyClassRector\Fixture;

final class OnlyReadonlyProperty2
{
   public function __construct(private readonly string $property)
   {
   }
}

?>
-----
<?php

namespace Rector\Tests\Php82\Rector\Class_\ReadOnlyClassRector\Fixture;

final readonly class OnlyReadonlyProperty2
{
   public function __construct(private string $property)
   {
   }
}

?>
