<?php

namespace Rector\CodingStyle\Tests\Rector\Class_\AddArrayDefaultToArrayPropertyRector\Fixture;

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

namespace Rector\CodingStyle\Tests\Rector\Class_\AddArrayDefaultToArrayPropertyRector\Fixture;

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
