<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedPropertyRector\Fixture;

final class SkipDocType
{
    /**
     * @var int
     */
    private $age = 100;

    public function getAge()
    {
        return $this->age;
    }
}
