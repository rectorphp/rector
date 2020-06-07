<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

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

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

final class DefaultValuesForNullableArray
{
    private ?array $rooms = null;
}

?>
