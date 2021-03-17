<?php

namespace Rector\Tests\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector\Fixture;

class Fixture
{
    /**
     * @var int[]
     */
    private $values;

    public function isEmpty()
    {
        return $this->values === null;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector\Fixture;

class Fixture
{
    /**
     * @var int[]
     */
    private $values = [];

    public function isEmpty()
    {
        return $this->values === [];
    }
}

?>
