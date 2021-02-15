<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeFromStrictTypedPropertyRector\Fixture;

final class SkipNullableType
{
    private ?int $age;

    public function setAge($age)
    {
        $this->age = $age;
    }
}
