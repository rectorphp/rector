<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

class SkipNullType
{
    /**
     * @var null|mixed
     */
    protected $property;

    function some()
    {
	    $this->property = null;
	}
}
