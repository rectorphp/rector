<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedPropertyRector\Fixture;

final class SkipSingleVoid
{
    private int $age = 100;

    public function getAge()
    {
        if (mt_rand(0, 100)) {
            return;
        }

        return $this->age;
    }
}
