<?php

namespace Rector\Order\Tests\Rector\ClassLike\OrderPropertyByComplexityRector\Fixture;

use Rector\Order\Tests\Rector\ClassLike\OrderPropertyByComplexityRector\Source\ComplexType;

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

namespace Rector\Order\Tests\Rector\ClassLike\OrderPropertyByComplexityRector\Fixture;

use Rector\Order\Tests\Rector\ClassLike\OrderPropertyByComplexityRector\Source\ComplexType;

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
