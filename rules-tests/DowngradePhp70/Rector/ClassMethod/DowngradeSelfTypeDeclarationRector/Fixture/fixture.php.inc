<?php

namespace Rector\Tests\DowngradePhp70\Rector\ClassMethod\DowngradeSelfTypeDeclarationRector\Fixture;

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

namespace Rector\Tests\DowngradePhp70\Rector\ClassMethod\DowngradeSelfTypeDeclarationRector\Fixture;

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
