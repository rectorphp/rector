<?php

namespace Rector\Tests\Privatization\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector\Fixture;

class SkipClassExtended extends AbstractParentClass
{
    /**
     * @api
     */
    public function stop()
    {
        $this->value;
    }
}

abstract class AbstractParentClass
{
    public $value;
}
