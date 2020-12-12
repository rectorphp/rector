<?php

namespace Rector\CodingStyle\Tests\Rector\Property\ArrayPropertyDefaultValueRector\Fixture;

class StaticProperty
{
    /** @var int[] */
    public static $property;

    /** @var int[][] */
    public $doubleArray;
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Property\ArrayPropertyDefaultValueRector\Fixture;

class StaticProperty
{
    /** @var int[] */
    public static $property = [];

    /** @var int[][] */
    public $doubleArray = [];
}

?>
