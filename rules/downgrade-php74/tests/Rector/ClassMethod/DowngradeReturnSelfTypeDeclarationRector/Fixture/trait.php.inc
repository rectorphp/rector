<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeReturnSelfTypeDeclarationRector\Fixture;

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

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeReturnSelfTypeDeclarationRector\Fixture;

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
