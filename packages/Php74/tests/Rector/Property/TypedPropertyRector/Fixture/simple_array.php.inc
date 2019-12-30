<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

final class SimpleArray
{
    /**
     * @var array
     */
    private $foo;
}

?>
-----
<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

final class SimpleArray
{
    private array $foo;
}

?>
