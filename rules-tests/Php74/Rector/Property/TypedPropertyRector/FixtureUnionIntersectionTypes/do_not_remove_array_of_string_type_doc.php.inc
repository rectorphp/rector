<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureUnionIntersectionTypes;

final class DoNotRemoveArrayOfStringTypeDoc
{
    /**
     * @var string|string[]|null
     */
    private $property;
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureUnionIntersectionTypes;

final class DoNotRemoveArrayOfStringTypeDoc
{
    /**
     * @var string|string[]|null
     */
    private string|array|null $property = null;
}

?>
