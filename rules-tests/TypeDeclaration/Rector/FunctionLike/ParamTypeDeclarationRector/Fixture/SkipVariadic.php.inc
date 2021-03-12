<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

class SkipVariadic
{
    /**
     * @param mixed[] $values
     */
    public function someFunction(...$values)
    {
    }
}
