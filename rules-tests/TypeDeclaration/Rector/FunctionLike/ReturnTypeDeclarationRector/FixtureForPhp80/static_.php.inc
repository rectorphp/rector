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

    public function getNullableStatic(): ?static
    {
        if (rand(0,1)) {
            return new static();
        }

        return null;
    }
}

?>
