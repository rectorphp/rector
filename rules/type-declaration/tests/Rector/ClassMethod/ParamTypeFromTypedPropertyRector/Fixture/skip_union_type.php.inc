<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeFromTypedPropertyRector\Fixture;

final class SkipUnionType
{
    private int|string $age;

    public function setAge($age)
    {
        $this->age = $age;
    }
}
