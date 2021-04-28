<?php

namespace Rector\Tests\TypeDeclaration\Rector\Param\ParamTypeFromStrictTypedPropertyRector\Fixture;

final class SkipUnionType
{
    private int|string $age;

    public function setAge($age)
    {
        $this->age = $age;
    }
}
