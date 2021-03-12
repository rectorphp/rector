<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedPropertyRector\Fixture;

final class SkipOneMissingType
{
    private int $age = 100;

    /**
     * @var string
     */
    private $name = 'Elon';

    public function getValue()
    {
        if (mt_rand(0, 100)) {
            return $this->age;
        }

        return $this->name;
    }
}
