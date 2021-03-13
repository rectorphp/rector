<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class ANewClass
{
    public function getObject()
    {
        return new class() {};
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class ANewClass
{
    public function getObject(): object
    {
        return new class() {};
    }
}

?>
