<?php

namespace Rector\Order\Tests\Rector\Class_\OrderPropertyByComplexityRector\Fixture;

use Rector\Order\Tests\Rector\Class_\OrderPropertyByComplexityRector\Source\ComplexType;

class SomeClass
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var ComplexType
     */
    private $service;

    /**
     * @var int
     */
    private $price;
}

?>
-----
<?php

namespace Rector\Order\Tests\Rector\Class_\OrderPropertyByComplexityRector\Fixture;

use Rector\Order\Tests\Rector\Class_\OrderPropertyByComplexityRector\Source\ComplexType;

class SomeClass
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $price;
    /**
     * @var ComplexType
     */
    private $service;
}

?>
