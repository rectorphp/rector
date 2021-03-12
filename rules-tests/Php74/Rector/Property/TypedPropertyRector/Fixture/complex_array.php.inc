<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\SomeParent;

final class ComplexArray
{
    /**
     * @var array<int, string>
     */
    private $foo;

    /**
     * @var int[]
     */
    private $foos;
}

?>
-----
<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\SomeParent;

final class ComplexArray
{
    /**
     * @var array<int, string>
     */
    private array $foo;

    /**
     * @var int[]
     */
    private array $foos;
}

?>
