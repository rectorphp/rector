<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class IncludingAssign extends AbstractKernelTestCase
{
    private $someService;

    protected function setUp(): void
    {
        $this->someService = $this->getService('Some');
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class IncludingAssign extends AbstractKernelTestCase
{
    protected function setUp(): void
    {
    }
}

?>
