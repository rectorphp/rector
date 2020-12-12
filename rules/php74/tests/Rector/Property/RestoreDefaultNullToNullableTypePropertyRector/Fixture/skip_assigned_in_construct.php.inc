<?php

namespace Rector\Php74\Tests\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector\Fixture;

class SkipAssignedInConstruct
{
    public ?string $name;

    public function __construct(?string $name = null)
    {
        $this->name = $name;
    }
}
