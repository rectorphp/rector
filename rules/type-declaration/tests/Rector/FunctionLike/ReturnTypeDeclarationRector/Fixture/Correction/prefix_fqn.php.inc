<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture\Correction;

use Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Source\RealReturnedClass;
use Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Source\ReturnedClass;

class PrefixFqn
{
    public function getReturnedClass(): ReturnedClass
    {
        return new RealReturnedClass();
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture\Correction;

use Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Source\RealReturnedClass;
use Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Source\ReturnedClass;

class PrefixFqn
{
    public function getReturnedClass(): \Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Source\RealReturnedClass
    {
        return new RealReturnedClass();
    }
}

?>
