<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeAbstractPrivateMethodInTraitRector\Fixture;

trait WithAbstractPrivate
{
    abstract private function someAbstractPrivateFunction();
}
?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeAbstractPrivateMethodInTraitRector\Fixture;

trait WithAbstractPrivate
{
    private function someAbstractPrivateFunction()
    {
    }
}
?>
