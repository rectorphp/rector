<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeFromCallersRector\Fixture;

use Rector\Core\Rector\AbstractRector;
use Rector\PostRector\Rector\AbstractPostRector;

final class SomeRectorPass
{
    public function run(AbstractRector $abstractRector, AbstractPostRector $postRector)
    {
        $this->setValue($abstractRector);
        $this->setValue($postRector);
    }

    private function setValue($rector)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeFromCallersRector\Fixture;

use Rector\Core\Rector\AbstractRector;
use Rector\PostRector\Rector\AbstractPostRector;

final class SomeRectorPass
{
    public function run(AbstractRector $abstractRector, AbstractPostRector $postRector)
    {
        $this->setValue($abstractRector);
        $this->setValue($postRector);
    }

    private function setValue(\Rector\Core\Contract\Rector\RectorInterface $rector)
    {
    }
}

?>
