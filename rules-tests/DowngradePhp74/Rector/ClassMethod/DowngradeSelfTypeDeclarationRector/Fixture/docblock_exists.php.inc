<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeSelfTypeDeclarationRector\Fixture;

class DocblockExists {
    /**
     * This property is the best one
     */
    public function getSelf(): self
    {
        return $this;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeSelfTypeDeclarationRector\Fixture;

class DocblockExists {
    /**
     * This property is the best one
     * @return $this
     */
    public function getSelf()
    {
        return $this;
    }
}

?>
