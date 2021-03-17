<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class DefaultValuesForNullableArray
{
    /**
     * @var mixed[]|null
     */
    private $rooms;
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class DefaultValuesForNullableArray
{
    private ?array $rooms = null;
}

?>
