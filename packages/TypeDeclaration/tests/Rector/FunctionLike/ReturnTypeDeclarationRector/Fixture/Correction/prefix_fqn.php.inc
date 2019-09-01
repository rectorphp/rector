<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Correction;

use Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Source\RealReturnedClass;
use Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Source\ReturnedClass;

class SkipOverrideOfTheSameClass
{
    public function getReturnedClass(): ReturnedClass
    {
        return new RealReturnedClass();
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Correction;

use Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Source\RealReturnedClass;
use Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Source\ReturnedClass;

class SkipOverrideOfTheSameClass
{
    public function getReturnedClass(): RealReturnedClass
    {
        return new RealReturnedClass();
    }
}

?>
