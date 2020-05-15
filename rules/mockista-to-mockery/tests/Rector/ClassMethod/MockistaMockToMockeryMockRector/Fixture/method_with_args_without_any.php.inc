<?php

namespace Rector\MockistaToMockery\Tests\Rector\ClassMethod\MockistaMockToMockeryMockRector\Fixture;

use PHPUnit\Framework\TestCase;
use Rector\MockistaToMockery\Tests\Rector\ClassMethod\MockistaMockToMockeryMockRector\Source\User;

class MethodWithArgsWithoutAny extends TestCase
{
    public function testSomething()
    {
        $mockUser = mock(User::class);
        $mockUser->methodWithArg()->once();
    }
}

?>
-----
<?php

namespace Rector\MockistaToMockery\Tests\Rector\ClassMethod\MockistaMockToMockeryMockRector\Fixture;

use PHPUnit\Framework\TestCase;
use Rector\MockistaToMockery\Tests\Rector\ClassMethod\MockistaMockToMockeryMockRector\Source\User;

class MethodWithArgsWithoutAny extends TestCase
{
    public function testSomething()
    {
        $mockUser = \Mockery::mock(User::class);
        $mockUser->expects()->methodWithArg()->once()->withAnyArgs();
    }
}

?>
