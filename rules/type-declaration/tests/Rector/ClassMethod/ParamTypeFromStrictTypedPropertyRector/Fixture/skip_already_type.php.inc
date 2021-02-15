<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeFromStrictTypedPropertyRector\Fixture;

final class SkipAlreadyType
{
    private int $age;

    public function setAge(string $age)
    {
        $this->age = $age;
    }
}
