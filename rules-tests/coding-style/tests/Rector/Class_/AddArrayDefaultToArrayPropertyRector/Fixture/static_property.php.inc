<?php

namespace Rector\CodingStyle\Tests\Rector\Class_\AddArrayDefaultToArrayPropertyRector\Fixture;

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

namespace Rector\CodingStyle\Tests\Rector\Class_\AddArrayDefaultToArrayPropertyRector\Fixture;

class StaticProperty
{
    /** @var int[] */
    public static $property = [];

    /** @var int[][] */
    public $doubleArray = [];
}

?>
