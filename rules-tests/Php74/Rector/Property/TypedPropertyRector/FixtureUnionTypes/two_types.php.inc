<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureUnionTypes;

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

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureUnionTypes;

final class TwoTypes
{
    private bool|string $unionValue;
}

?>
