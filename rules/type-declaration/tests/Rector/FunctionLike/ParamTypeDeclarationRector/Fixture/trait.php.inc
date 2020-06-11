<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture;

trait SomeTrait
{

}

class SomeClass
{
    /**
     * @param SomeTrait $param
     */
    function someFunction($param)
    {
    }
}

?>
