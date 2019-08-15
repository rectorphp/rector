<?php

namespace Rector\CodingStyle\Tests\Rector\Class_\AddArrayDefaultToArrayPropertyRector\Fixture;

class SkipNullableArray
{
    /**
     * @var int[]|null
     */
    private $values;

    public function isEmpty()
    {
        return $this->values === null;
    }
}
