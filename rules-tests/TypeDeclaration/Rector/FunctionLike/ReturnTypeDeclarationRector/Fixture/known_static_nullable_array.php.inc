<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class KnownStaticNullableArray
{
    public function getMoreItems()
    {
        if ((bool) rand(0, 100)) {
            return null;
        }

        return [];
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class KnownStaticNullableArray
{
    public function getMoreItems(): ?array
    {
        if ((bool) rand(0, 100)) {
            return null;
        }

        return [];
    }
}

?>
