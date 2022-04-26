<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureUnionIntersectionTypes;

final class NullFalseOtherType
{
    /**
     * @var null|false|\stdClass
     */
    private $unionValue;
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureUnionIntersectionTypes;

final class NullFalseOtherType
{
    private null|false|\stdClass $unionValue = null;
}

?>
