<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeFromStrictTypedPropertyRector\Fixture;

final class SkipDocType
{
    /**
     * @var int
     */
    private $age;

    public function setAge($age)
    {
        $this->age = $age;
    }
}
