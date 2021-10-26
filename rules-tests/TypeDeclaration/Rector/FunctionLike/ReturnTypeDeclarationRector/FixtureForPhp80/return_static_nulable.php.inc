<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\FixtureForPhp80;

final class ReturnStaticNullable
{
    public function getNullableStatic()
    {
        if (rand(0,1)) {
            return new static();
        }

        return null;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\FixtureForPhp80;

final class ReturnStaticNullable
{
    public function getNullableStatic(): ?static
    {
        if (rand(0,1)) {
            return new static();
        }

        return null;
    }
}

?>
