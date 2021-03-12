<?php

namespace Rector\Tests\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector\Fixture;

class TwoTypes
{
    /**
     * @var Bar[]|Foo[]
     */
    private $combined;
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector\Fixture;

class TwoTypes
{
    /**
     * @var Bar[]|Foo[]
     */
    private $combined = [];
}

?>
