<?php

namespace Rector\CodingStyle\Tests\Rector\Class_\AddArrayDefaultToArrayPropertyRector\Fixture;

class Fixture2
{
    /**
     * @var int[]
     */
    private $items;

    /**
     * @var array|SomeEntity[]
     */
    public $entities;

    /**
     * @var Bar[]|Foo[]
     */
    private $combined;

    public function run()
    {
        foreach ($items as $item) {
        }
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Class_\AddArrayDefaultToArrayPropertyRector\Fixture;

class Fixture2
{
    /**
     * @var int[]
     */
    private $items = [];

    /**
     * @var array|SomeEntity[]
     */
    public $entities = [];

    /**
     * @var Bar[]|Foo[]
     */
    private $combined = [];

    public function run()
    {
        foreach ($items as $item) {
        }
    }
}

?>
