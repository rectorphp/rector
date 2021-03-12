<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\FixtureUnionTypes;

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

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\FixtureUnionTypes;

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
