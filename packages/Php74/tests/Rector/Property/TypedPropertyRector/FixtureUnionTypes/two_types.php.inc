<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\FixtureUnionTypes;

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

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\FixtureUnionTypes;

final class TwoTypes
{
    private bool|string $unionValue;
}

?>
