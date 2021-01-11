<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeReturnSelfTypeDeclarationRector\Fixture;

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

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeReturnSelfTypeDeclarationRector\Fixture;

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
