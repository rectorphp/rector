<?php

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Fixture;

use Mockery;
use PHPUnit\Framework\TestCase;

class JustMocksTest extends TestCase
{
    public function test()
    {
        $mockNetteUser = Mockery::mock(User::class);
        $mockNetteUser->expects()->isAllowed('yes', 'vote');
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Fixture;

use Mockery;
use PHPUnit\Framework\TestCase;

class JustMocksTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function test()
    {
        $mockNetteUser = Mockery::mock(User::class);
        $mockNetteUser->expects()->isAllowed('yes', 'vote');
    }
}

?>
