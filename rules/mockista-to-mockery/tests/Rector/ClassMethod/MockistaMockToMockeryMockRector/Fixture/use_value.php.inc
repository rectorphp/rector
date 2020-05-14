<?php

namespace Rector\MockistaToMockery\Tests\Rector\ClassMethod\MockistaMockToMockeryMockRector\Fixture;

use PHPUnit\Framework\TestCase;
use Rector\MockistaToMockery\Tests\Rector\ClassMethod\MockistaMockToMockeryMockRector\Source\User;

class UseValue extends TestCase
{
    public function testSomething()
    {
        $mockUser = mock(User::class);
        $mockUser->id = 5;
        $mockUser->run($mockUser->id);
    }
}

?>
-----
<?php

namespace Rector\MockistaToMockery\Tests\Rector\ClassMethod\MockistaMockToMockeryMockRector\Fixture;

use PHPUnit\Framework\TestCase;
use Rector\MockistaToMockery\Tests\Rector\ClassMethod\MockistaMockToMockeryMockRector\Source\User;

class UseValue extends TestCase
{
    public function testSomething()
    {
        $mockUser = \Mockery::mock(User::class);
        $mockUser->id = 5;
        $mockUser->expects()->run($mockUser->id);
    }
}

?>
