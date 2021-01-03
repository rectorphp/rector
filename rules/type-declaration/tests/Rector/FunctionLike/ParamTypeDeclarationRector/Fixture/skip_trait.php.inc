<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

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
