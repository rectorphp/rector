<?php

namespace Rector\Php74\Tests\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector\Fixture;

class SkipAssignedInConstructor
{
    public ?string $name;

    public function __construct(?string $name = null)
    {
        $this->name = $name;
    }
}
