<?php

namespace Rector\Tests\DowngradePhp70\Rector\ClassMethod\DowngradeParentTypeDeclarationRector\Fixture;

use Rector\Tests\DowngradePhp70\Rector\ClassMethod\DowngradeParentTypeDeclarationRector\Source\SomeParentClass;

final class Fixture extends SomeParentClass
{
    public function getParent(): parent
    {
        return $this;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\ClassMethod\DowngradeParentTypeDeclarationRector\Fixture;

use Rector\Tests\DowngradePhp70\Rector\ClassMethod\DowngradeParentTypeDeclarationRector\Source\SomeParentClass;

final class Fixture extends SomeParentClass
{
    /**
     * @return parent
     */
    public function getParent()
    {
        return $this;
    }
}

?>
