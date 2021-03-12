<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeSelfTypeDeclarationRector\Fixture;

class NullableType
{
    public function getSelfOrNull(): ?self
    {
        return $this;
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeSelfTypeDeclarationRector\Fixture;

class NullableType
{
    /**
     * @return $this|null
     */
    public function getSelfOrNull()
    {
        return $this;
    }
}

?>
