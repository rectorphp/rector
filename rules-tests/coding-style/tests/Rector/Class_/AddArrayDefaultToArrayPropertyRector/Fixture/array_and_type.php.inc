<?php

namespace Rector\CodingStyle\Tests\Rector\Class_\AddArrayDefaultToArrayPropertyRector\Fixture;

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

namespace Rector\CodingStyle\Tests\Rector\Class_\AddArrayDefaultToArrayPropertyRector\Fixture;

class ArrayAndType
{
    /**
     * @var array|SomeEntity[]
     */
    public $entities = [];
}

?>
