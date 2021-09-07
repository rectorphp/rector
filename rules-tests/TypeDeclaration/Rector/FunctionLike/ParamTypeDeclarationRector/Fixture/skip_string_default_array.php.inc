<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

class SkipStringDefaultArray
{
    /**
     * @param string $value
     */
    public function someFunction($value = [])
    {
    }
}
