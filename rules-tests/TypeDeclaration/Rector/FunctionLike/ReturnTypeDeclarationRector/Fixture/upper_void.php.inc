<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class UpperVoid
{
    public function upperVoid()
    {
        function toString()
        {
            return 'test';
        };
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class UpperVoid
{
    public function upperVoid(): void
    {
        function toString(): string
        {
            return 'test';
        };
    }
}

?>
