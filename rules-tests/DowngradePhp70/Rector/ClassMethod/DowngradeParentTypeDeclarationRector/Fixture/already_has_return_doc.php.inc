<?php

namespace Rector\Tests\DowngradePhp70\Rector\ClassMethod\DowngradeParentTypeDeclarationRector\Fixture;

class ParentClass
{
}

class AlreadyHasReturnDoc extends ParentClass
{
    /**
     * @return parent
     */
    public function getParent(): parent
    {
        return $this;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\ClassMethod\DowngradeParentTypeDeclarationRector\Fixture;

class ParentClass
{
}

class AlreadyHasReturnDoc extends ParentClass
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
