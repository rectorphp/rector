<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\FixtureForPhp80;

class Static_
{
    /**
     * @return static
     */
    public function getSelf()
    {
        return $this;
    }

    public function getStatic()
    {
        return new static();
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\FixtureForPhp80;

class Static_
{
    /**
     * @return static
     */
    public function getSelf(): static
    {
        return $this;
    }

    public function getStatic(): static
    {
        return new static();
    }
}

?>
