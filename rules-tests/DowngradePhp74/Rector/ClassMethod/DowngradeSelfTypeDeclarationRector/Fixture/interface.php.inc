<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeSelfTypeDeclarationRector\Fixture;

interface WithSelfInterface
{
    public function getSelfOrNull(): self;
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeSelfTypeDeclarationRector\Fixture;

interface WithSelfInterface
{
    /**
     * @return $this
     */
    public function getSelfOrNull();
}

?>
