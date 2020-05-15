<?php

namespace Rector\MockistaToMockery\Tests\Rector\ClassMethod\MockistaMockToMockeryMockRector\Fixture;

use PHPUnit\Framework\TestCase;

class MethodArgs extends TestCase
{
    public function testSomething()
    {
        $mockUser = mock(User::class);
        $mockUser->getId(1);
    }
}

?>
-----
<?php

namespace Rector\MockistaToMockery\Tests\Rector\ClassMethod\MockistaMockToMockeryMockRector\Fixture;

use PHPUnit\Framework\TestCase;

class MethodArgs extends TestCase
{
    public function testSomething()
    {
        $mockUser = \Mockery::mock(User::class);
        $mockUser->expects()->getId(1);
    }
}

?>
