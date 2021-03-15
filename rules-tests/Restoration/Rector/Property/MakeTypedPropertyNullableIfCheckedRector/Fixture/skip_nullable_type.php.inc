<?php

namespace Rector\Tests\Restoration\Rector\Property\MakeTypedPropertyNullableIfCheckedRector\Fixture;

use Rector\Tests\Restoration\Rector\Property\MakeTypedPropertyNullableIfCheckedRector\Source\PropertyTypeClass;

final class SkipNullableType
{
    private ?PropertyTypeClass $propertyTypeClass = null;

    public function run()
    {
        if ($this->propertyTypeClass === null) {
            $this->propertyTypeClass = new PropertyTypeClass;
        }
    }
}
