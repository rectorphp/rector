<?php

namespace Rector\MockistaToMockery\Tests\Rector\ClassMethod\MockistaMockToMockeryMockRector\Fixture;

use PHPUnit\Framework\TestCase;

class SomeTest extends TestCase
{
    public function testSomething()
    {
        $mockUser = mock(User::class);
        $mockUser->getId()->once->andReturn(1);
    }
}

?>
-----
<?php

namespace Rector\MockistaToMockery\Tests\Rector\ClassMethod\MockistaMockToMockeryMockRector\Fixture;

use PHPUnit\Framework\TestCase;

class SomeTest extends TestCase
{
    public function testSomething()
    {
        $mockUser = \Mockery::mock(User::class);
        $mockUser->expects()->getId()->once()->andReturn(1);
    }
}

?>
