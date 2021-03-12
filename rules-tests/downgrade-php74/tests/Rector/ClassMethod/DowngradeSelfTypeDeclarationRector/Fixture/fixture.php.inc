<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeSelfTypeDeclarationRector\Fixture;

class Fixture
{
    public function getSelf(): self
    {
        return $this;
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeSelfTypeDeclarationRector\Fixture;

class Fixture
{
    /**
     * @return $this
     */
    public function getSelf()
    {
        return $this;
    }
}

?>
