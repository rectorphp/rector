<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class KnownStaticNullable
{
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
    public function getStringNull(): ?string
    {
        /** @var string|null $value */
        $value = 'hi';

        return $value;
    }
}

?>
