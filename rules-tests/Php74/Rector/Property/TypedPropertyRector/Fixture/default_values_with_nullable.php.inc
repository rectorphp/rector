<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

final class DefaultValuesWithNullable
{
    /**
     * @var bool
     */
    private $isItRealNameNull = null;
}

?>
-----
<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

final class DefaultValuesWithNullable
{
    private ?bool $isItRealNameNull = null;
}

?>
