<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

class NullableResourceTypes
{
    /**
     * @param resource|null $value
     */
    public function someFunction($value)
    {
    }
}
