<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

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

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class DefaultValuesWithNullable
{
    private ?bool $isItRealNameNull = null;
}

?>
