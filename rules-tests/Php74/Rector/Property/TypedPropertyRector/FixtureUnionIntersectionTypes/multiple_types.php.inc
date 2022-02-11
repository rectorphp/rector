<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureUnionIntersectionTypes;

final class MultipleTypes
{
    /**
     * @var string|int|float
     */
    private $nonNullableVariable;
    
    /**
     * @var string|int|float|null
     */
    private $nullableVariable;
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureUnionIntersectionTypes;

final class MultipleTypes
{
    private float|int|string $nonNullableVariable;
    
    private float|int|string|null $nullableVariable = null;
}

?>
