<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeFromTypedPropertyRector\Fixture;

final class SkipAlreadyType
{
    private int $age;

    public function setAge(string $age)
    {
        $this->age = $age;
    }
}
