<?php

namespace Rector\Tests\Php81\Rector\Property\ReadOnlyPropertyRector\Fixture;

use ArrayObject;

final class SkipArrayDimFetchFromArrayObjectInAssignVar
{
    private ArrayObject $array;

    public function __construct()
    {
        $this->array = new ArrayObject();
    }

    public function getOrSet(int|string $key, mixed $value = null): mixed
    {
        return $this->array[$key] ??= $value;
    }
}
