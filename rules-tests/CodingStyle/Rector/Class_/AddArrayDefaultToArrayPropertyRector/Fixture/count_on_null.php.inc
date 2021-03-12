<?php

namespace Rector\CodingStyle\Tests\Rector\Class_\AddArrayDefaultToArrayPropertyRector\Fixture;

class CountOnNull
{
    /**
     * @var int[]
     */
    private $values;

    public function isEmpty()
    {
        return $this->values !== null && count($this->values) > 0;
    }

    public function isEmptyOtherSide()
    {
        return null !== $this->values && count($this->values);
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Class_\AddArrayDefaultToArrayPropertyRector\Fixture;

class CountOnNull
{
    /**
     * @var int[]
     */
    private $values = [];

    public function isEmpty()
    {
        return count($this->values) > 0;
    }

    public function isEmptyOtherSide()
    {
        return count($this->values);
    }
}

?>
