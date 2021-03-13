<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeSelfTypeDeclarationRector\Fixture;

trait WithSelfTrait
{
    public function getSelfOrNull(): self
    {
        return $this;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeSelfTypeDeclarationRector\Fixture;

trait WithSelfTrait
{
    /**
     * @return $this
     */
    public function getSelfOrNull()
    {
        return $this;
    }
}

?>
