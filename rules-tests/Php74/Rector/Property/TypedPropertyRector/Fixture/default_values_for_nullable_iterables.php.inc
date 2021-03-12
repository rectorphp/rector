<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class DefaultValuesForNullableIterables
{
    /**
     * @var array
     */
    private $items = null;

    /**
     * @var iterable
     */
    private $itemsB = null;

    /**
     * @var array|null
     */
    private $nullableItems = null;
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class DefaultValuesForNullableIterables
{
    private ?array $items = null;

    private ?iterable $itemsB = null;

    private ?array $nullableItems = null;
}

?>
