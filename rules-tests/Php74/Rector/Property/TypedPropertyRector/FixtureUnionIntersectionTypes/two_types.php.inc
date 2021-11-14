<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureUnionIntersectionTypes;

final class TwoTypes
{
    /**
     * @var bool|string
     */
    private $unionValue;
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureUnionIntersectionTypes;

final class TwoTypes
{
    private bool|string $unionValue;
}

?>
