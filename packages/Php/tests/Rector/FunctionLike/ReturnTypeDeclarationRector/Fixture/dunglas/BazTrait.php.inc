<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Dunglas;

trait BazTrait
{
    /**
     * @return \DateTime
     */
    protected function inTrait($a)
    {
    }
}
?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Dunglas;

trait BazTrait
{
    /**
     * @return \DateTime
     */
    protected function inTrait($a): \DateTime
    {
    }
}
?>
