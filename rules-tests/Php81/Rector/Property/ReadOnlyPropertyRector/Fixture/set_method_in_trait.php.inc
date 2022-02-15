<?php

namespace Rector\Tests\Php81\Rector\Property\ReadOnlyPropertyRector\Fixture;

use stdClass;

trait SetMethodInTrait
{
    private stdClass $property;

    public function getProperty(): stdClass
    {
        return $this->property;
    }

    public function setProperty(stdClass $property): void
    {
        $this->property = $property;
    }
}
