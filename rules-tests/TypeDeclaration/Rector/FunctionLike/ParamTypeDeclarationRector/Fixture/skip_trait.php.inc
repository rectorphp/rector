<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

trait SkipTrait
{

}

class SomeClass
{
    /**
     * @param SkipTrait $param
     */
    function someFunction($param)
    {
    }
}
