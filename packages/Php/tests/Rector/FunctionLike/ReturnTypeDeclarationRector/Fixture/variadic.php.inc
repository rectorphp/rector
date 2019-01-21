<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

class VariadicTypes
{
    /**
     * @param mixed[] $values
     */
    public function someFunction(...$values)
    {
    }
}
