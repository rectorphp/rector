<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeStaticTypeDeclarationRector\Fixture;

trait AlsoTrait
{
    public function getAnything(): static
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeStaticTypeDeclarationRector\Fixture;

trait AlsoTrait
{
    /**
     * @return $this
     */
    public function getAnything()
    {
    }
}

?>
