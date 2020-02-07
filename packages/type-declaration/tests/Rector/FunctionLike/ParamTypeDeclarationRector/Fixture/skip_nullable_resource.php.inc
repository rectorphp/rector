<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture;

class NullableResourceTypes
{
    /**
     * @param resource|null $value
     */
    public function someFunction($value)
    {
    }
}
