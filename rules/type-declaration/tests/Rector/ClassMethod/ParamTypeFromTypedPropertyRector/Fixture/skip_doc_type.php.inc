<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeFromTypedPropertyRector\Fixture;

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
