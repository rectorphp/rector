<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class DoNotRemoveGenericTypeNullable
{
    /** @var array<string,mixed>|null */
    private $property;

    /** @var array<string>|null */
    private $property2;
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class DoNotRemoveGenericTypeNullable
{
    /** @var array<string,mixed>|null */
    private ?array $property = null;

    /** @var array<string>|null */
    private ?array $property2 = null;
}

?>
