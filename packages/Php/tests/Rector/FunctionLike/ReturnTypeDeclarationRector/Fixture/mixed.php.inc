<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

class MixedTypes
{
    /**
     * @param mixed|mixed[] $value
     */
    public function someFunction($value)
    {
    }
}
