<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class SkipArrayTypeFilledDefaultNull
{
    /**
     * @var array
     */
    private $property;

    public function __construct(array $property = null)
    {
        $this->property = $property;
    }
}
