<?php

namespace Rector\MockistaToMockery\Tests\Rector\Class_\MockeryTearDownRector\Fixture;

use PHPUnit\Framework\TestCase;

class AddToExistingTearDownTest extends TestCase
{
    public function test()
    {
        $mockUser = mock(User::class);
    }
    protected function tearDown(): void
    {
        $value = 5;
    }
}

?>
-----
<?php

namespace Rector\MockistaToMockery\Tests\Rector\Class_\MockeryTearDownRector\Fixture;

use PHPUnit\Framework\TestCase;

class AddToExistingTearDownTest extends TestCase
{
    public function test()
    {
        $mockUser = mock(User::class);
    }
    protected function tearDown(): void
    {
        $value = 5;
        \Mockery::close();
    }
}

?>
