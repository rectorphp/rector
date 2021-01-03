<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

class SkipMixedTypes
{
    /**
     * @param mixed|mixed[] $value
     */
    public function someFunction($value)
    {
    }
}
