<?php

namespace Rector\Tests\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector\Fixture;

class ScalarInteger
{
    /**
     * @var int[]
     */
    private $items;

    public function run()
    {
        foreach ($items as $item) {
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector\Fixture;

class ScalarInteger
{
    /**
     * @var int[]
     */
    private $items = [];

    public function run()
    {
        foreach ($items as $item) {
        }
    }
}

?>
