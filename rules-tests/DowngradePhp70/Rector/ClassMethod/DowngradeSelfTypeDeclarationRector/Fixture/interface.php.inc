<?php

namespace Rector\Tests\DowngradePhp70\Rector\ClassMethod\DowngradeSelfTypeDeclarationRector\Fixture;

interface WithSelfInterface
{
    public function getSelfOrNull(): self;
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\ClassMethod\DowngradeSelfTypeDeclarationRector\Fixture;

interface WithSelfInterface
{
    /**
     * @return $this
     */
    public function getSelfOrNull();
}

?>
