<?php

namespace Rector\Php\Tests\Rector\Property\TypedPropertyRector\Fixture;

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

namespace Rector\Php\Tests\Rector\Property\TypedPropertyRector\Fixture;

final class BoolProperty
{
    /**
     * @var bool
     * another comment
     */
    private bool $isTrue = false;
}

?>
