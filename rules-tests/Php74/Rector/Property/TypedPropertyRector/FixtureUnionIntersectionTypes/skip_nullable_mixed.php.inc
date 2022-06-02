<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureUnionIntersectionTypes;

class SkipNullableMixed
{
    /** @var mixed */
    private $transient;

    public function setTransient($value): void
    {
        if ($value !== $this->transient) {

        }
    }
}
