<?php

namespace Rector\Tests\TypeDeclaration\Rector\Param\ParamTypeFromStrictTypedPropertyRector\Fixture;

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
