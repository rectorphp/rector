<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

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

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class ANewClass
{
    public function getObject(): object
    {
        return new class() {};
    }
}

?>
