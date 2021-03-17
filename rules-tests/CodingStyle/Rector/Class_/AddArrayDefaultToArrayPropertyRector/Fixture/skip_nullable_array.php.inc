<?php

namespace Rector\Tests\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector\Fixture;

class SkipNullableArray
{
    /**
     * @var int[]|null
     */
    private $values;

    /** @var string[]|null */
    public $anArrayVariable;

    public function isEmpty()
    {
        return $this->values === null;
    }
}
