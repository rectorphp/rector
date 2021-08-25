<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

class SkipNull
{
    /**
     * @param null $value
     */
    public function someFunction($value)
    {
    }
}
