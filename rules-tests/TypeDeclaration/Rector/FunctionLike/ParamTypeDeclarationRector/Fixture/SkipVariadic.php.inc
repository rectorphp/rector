<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

class SkipVariadic
{
    /**
     * @param mixed[] $values
     */
    public function someFunction(...$values)
    {
    }
}
