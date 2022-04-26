<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureUnionIntersectionTypes;

final class RemoveArrayNoType
{
    /**
     * @var string|array|null
     */
    private $property;
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureUnionIntersectionTypes;

final class RemoveArrayNoType
{
    private string|array|null $property = null;
}

?>
