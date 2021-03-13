<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class KnownStaticVoid
{
    public function nothing()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class KnownStaticVoid
{
    public function nothing(): void
    {
    }
}

?>
