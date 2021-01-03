<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class KnownStaticNullable
{
    public function getMoreItems()
    {
        if ((bool) rand(0, 100)) {
            return null;
        }

        return [];
    }

    public function getStringNull()
    {
        /** @var string|null $value */
        $value = 'hi';

        return $value;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class KnownStaticNullable
{
    public function getMoreItems(): ?array
    {
        if ((bool) rand(0, 100)) {
            return null;
        }

        return [];
    }

    public function getStringNull(): ?string
    {
        /** @var string|null $value */
        $value = 'hi';

        return $value;
    }
}

?>
