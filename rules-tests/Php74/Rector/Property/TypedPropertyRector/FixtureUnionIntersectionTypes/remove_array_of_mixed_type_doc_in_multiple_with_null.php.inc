<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureUnionIntersectionTypes;

final class DoNotRemoveArrayOfMixedTypeDocInMultipleWithNull
{
    /**
     * @var string|mixed[]|null
     */
    private $property;
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureUnionIntersectionTypes;

final class DoNotRemoveArrayOfMixedTypeDocInMultipleWithNull
{
    /**
     * @var string|mixed[]|null
     */
    private array|string|null $property = null;
}

?>
