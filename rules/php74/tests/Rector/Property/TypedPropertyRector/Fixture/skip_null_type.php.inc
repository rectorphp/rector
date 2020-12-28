<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

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