<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\FixtureForPhp80;

class Static_
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

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\FixtureForPhp80;

class Static_
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
