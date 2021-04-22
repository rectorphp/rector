<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class IncludingAssign extends \Rector\Testing\PHPUnit\AbstractTestCase
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

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class IncludingAssign extends \Rector\Testing\PHPUnit\AbstractTestCase
{
    protected function setUp(): void
    {
    }
}

?>
