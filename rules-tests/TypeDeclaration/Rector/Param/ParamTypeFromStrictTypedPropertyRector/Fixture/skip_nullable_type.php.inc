<?php

namespace Rector\Tests\TypeDeclaration\Rector\Param\ParamTypeFromStrictTypedPropertyRector\Fixture;

final class SkipNullableType
{
    private ?int $age;

    public function setAge($age)
    {
        $this->age = $age;
    }
}
