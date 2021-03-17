<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class BoolProperty
{
    /**
     * @var bool
     * another comment
     */
    private $isTrue = false;
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class BoolProperty
{
    /**
     * another comment
     */
    private bool $isTrue = false;
}

?>
