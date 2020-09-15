<?php

namespace Rector\Order\Tests\Rector\Class_\OrderPropertyByComplexityRector\Fixture;

class VisibilityClass
{
    /**
     * @var array
     */
    private $array;

    /**
     * @var string
     */
    public $string;

    /**
     * @var int
     */
    private $integer;
}

?>
-----
<?php

namespace Rector\Order\Tests\Rector\Class_\OrderPropertyByComplexityRector\Fixture;

class VisibilityClass
{
    private $integer;
    /**
     * @var string
     */
    public $string;
    /**
     * @var array
     */
    private $array;
}

?>
