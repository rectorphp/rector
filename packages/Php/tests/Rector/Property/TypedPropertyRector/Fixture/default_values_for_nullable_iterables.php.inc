<?php

namespace Rector\Php\Tests\Rector\Property\TypedPropertyRector\Fixture;

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

namespace Rector\Php\Tests\Rector\Property\TypedPropertyRector\Fixture;

final class DefaultValuesForNullableIterables
{
    /**
     * @var array
     */
    private ?array $items = null;

    /**
     * @var iterable
     */
    private ?iterable $itemsB = null;

    /**
     * @var array|null
     */
    private ?array $nullableItems = null;
}

?>
