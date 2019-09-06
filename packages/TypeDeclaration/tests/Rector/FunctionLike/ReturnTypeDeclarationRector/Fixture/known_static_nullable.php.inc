<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

class KnownStaticNullable
{
    public function getMoreItems()
    {
        if (true) {
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

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

class KnownStaticNullable
{
    public function getMoreItems(): ?array
    {
        if (true) {
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
