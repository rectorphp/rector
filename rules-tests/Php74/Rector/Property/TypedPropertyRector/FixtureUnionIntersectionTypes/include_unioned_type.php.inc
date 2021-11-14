<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureUnionIntersectionTypes;

class IncludeUnionedType
{
    /**
     * @var bool|int
     */
    public $cantTouchThis = true;

    public function setNumber()
    {
        $this->cantTouchThis = 100;
    }

    /**
     * @return bool|int
     */
    public function getCantTouchThis()
    {
        return $this->cantTouchThis;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureUnionIntersectionTypes;

class IncludeUnionedType
{
    public bool|int $cantTouchThis = true;

    public function setNumber()
    {
        $this->cantTouchThis = 100;
    }

    /**
     * @return bool|int
     */
    public function getCantTouchThis()
    {
        return $this->cantTouchThis;
    }
}

?>
