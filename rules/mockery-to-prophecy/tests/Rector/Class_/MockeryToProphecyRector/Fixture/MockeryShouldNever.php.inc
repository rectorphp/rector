<?php

namespace Rector\MockeryToProphecy\Tests\Rector\Class_\MockeryToProphecyRector\Fixture;

use PHPUnit\Framework\TestCase;

final class MockeryShouldNever extends TestCase
{
    public function testSomething(): void
    {
        $mock = \Mockery::mock('MyClass');
        $mock->shouldReceive('create')->never();
    }
}

?>
-----
<?php

namespace Rector\MockeryToProphecy\Tests\Rector\Class_\MockeryToProphecyRector\Fixture;

use PHPUnit\Framework\TestCase;

final class MockeryShouldNever extends TestCase
{
    public function testSomething(): void
    {
        $mock = $this->prophesize('MyClass');
        $mock->create()->shouldNotBeCalled();
    }
}

?>
