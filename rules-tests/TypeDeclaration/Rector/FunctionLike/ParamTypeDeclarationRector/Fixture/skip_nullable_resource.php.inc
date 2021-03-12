<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

class NullableResourceTypes
{
    /**
     * @param resource|null $value
     */
    public function someFunction($value)
    {
    }
}
