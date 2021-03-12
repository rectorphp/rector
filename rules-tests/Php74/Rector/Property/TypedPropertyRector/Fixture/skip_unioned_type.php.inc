<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

class SkipUnionedType
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
