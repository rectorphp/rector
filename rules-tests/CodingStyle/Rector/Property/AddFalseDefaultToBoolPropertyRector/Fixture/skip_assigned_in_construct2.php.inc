<?php

namespace Rector\Tests\CodingStyle\Rector\Property\AddFalseDefaultToBoolPropertyRector\Fixture;

trait SkipAssignInConstruct2
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
