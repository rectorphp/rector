<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

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

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class SimpleArray
{
    private array $foo;
}

?>
