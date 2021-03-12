<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeSelfTypeDeclarationRector\Fixture;

interface WithSelfInterface
{
    public function getSelfOrNull(): self;
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeSelfTypeDeclarationRector\Fixture;

interface WithSelfInterface
{
    /**
     * @return $this
     */
    public function getSelfOrNull();
}

?>
