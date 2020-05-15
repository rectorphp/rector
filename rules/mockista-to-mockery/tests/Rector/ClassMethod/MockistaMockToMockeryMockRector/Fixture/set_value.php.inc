<?php

namespace Rector\MockistaToMockery\Tests\Rector\ClassMethod\MockistaMockToMockeryMockRector\Fixture;

use PHPUnit\Framework\TestCase;

class SetValue extends TestCase
{
    public function testSomething()
    {
        $mockUser = mock(User::class);
        $mockUser->id = 5;
    }
}

?>
-----
<?php

namespace Rector\MockistaToMockery\Tests\Rector\ClassMethod\MockistaMockToMockeryMockRector\Fixture;

use PHPUnit\Framework\TestCase;

class SetValue extends TestCase
{
    public function testSomething()
    {
        $mockUser = \Mockery::mock(User::class);
        $mockUser->id = 5;
    }
}

?>
