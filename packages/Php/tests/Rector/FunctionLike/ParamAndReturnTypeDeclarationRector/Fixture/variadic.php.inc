<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnTypeDeclarationRector\Fixture;

class VariadicTypes
{
    /**
     * @param mixed[] $values
     */
    public function someFunction(...$values)
    {
    }
}
