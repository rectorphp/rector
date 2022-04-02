<?php

namespace Rector\Tests\CodingStyle\Rector\Property\AddFalseDefaultToBoolPropertyRector\Fixture;

class SkipAssignInConstruct
{
    /**
     * @var bool
     */
    private $property;

    public function __construct(bool $property)
    {
        $this->property = $property;
    }
}
