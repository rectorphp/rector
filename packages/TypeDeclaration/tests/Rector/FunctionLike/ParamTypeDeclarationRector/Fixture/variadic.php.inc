<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture;

class VariadicTypes
{
    /**
     * @param mixed[] $values
     */
    public function someFunction(...$values)
    {
    }
}
