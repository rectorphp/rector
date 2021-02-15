<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeFromTypedPropertyRector\Fixture;

final class SkipNullableType
{
    private ?int $age;

    public function setAge($age)
    {
        $this->age = $age;
    }
}
