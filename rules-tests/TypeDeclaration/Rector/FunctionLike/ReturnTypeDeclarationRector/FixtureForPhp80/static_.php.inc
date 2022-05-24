<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\FixtureForPhp80;

final class Static_
{
    public function getStatic()
    {
        return new static();
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\FixtureForPhp80;

final class Static_
{
    public function getStatic(): static
    {
        return new static();
    }
}

?>