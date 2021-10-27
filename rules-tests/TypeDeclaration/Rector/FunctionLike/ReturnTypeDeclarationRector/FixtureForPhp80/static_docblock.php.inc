<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\FixtureForPhp80;

final class StaticDocBlock
{
    /**
     * @return static
     */
    public function getSelf()
    {
        return $this;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\FixtureForPhp80;

final class StaticDocBlock
{
    /**
     * @return static
     */
    public function getSelf(): static
    {
        return $this;
    }
}

?>
