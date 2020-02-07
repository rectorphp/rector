<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture;

class MixedTypes
{
    /**
     * @param mixed|mixed[] $value
     */
    public function someFunction($value)
    {
    }
}
