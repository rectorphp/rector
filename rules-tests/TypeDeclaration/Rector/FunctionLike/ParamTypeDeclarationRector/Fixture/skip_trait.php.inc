<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Source\SimpleTrait;

class SomeClass
{
    /**
     * @param SimpleTrait $param
     */
    function someFunction($param)
    {
    }
}
