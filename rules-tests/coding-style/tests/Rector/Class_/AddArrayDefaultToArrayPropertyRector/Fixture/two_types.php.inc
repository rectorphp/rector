<?php

namespace Rector\CodingStyle\Tests\Rector\Class_\AddArrayDefaultToArrayPropertyRector\Fixture;

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

namespace Rector\CodingStyle\Tests\Rector\Class_\AddArrayDefaultToArrayPropertyRector\Fixture;

class TwoTypes
{
    /**
     * @var Bar[]|Foo[]
     */
    private $combined = [];
}

?>
