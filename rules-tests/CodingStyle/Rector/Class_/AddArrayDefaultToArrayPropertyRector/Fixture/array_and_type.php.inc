<?php

namespace Rector\Tests\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector\Fixture;

class ArrayAndType
{
    /**
     * @var array|SomeEntity[]
     */
    public $entities;
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector\Fixture;

class ArrayAndType
{
    /**
     * @var array|SomeEntity[]
     */
    public $entities = [];
}

?>
