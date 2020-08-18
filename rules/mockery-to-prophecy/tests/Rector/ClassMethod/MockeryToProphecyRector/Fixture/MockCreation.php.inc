<?php

namespace Rector\MockeryToProphecy\Tests\Rector\ClassMethod\MockeryToProphecyRector\Fixture;

use PHPUnit\Framework\TestCase;

final class MockCreation extends TestCase
{
    public function testSomething()
    {
        $mock = \Mockery::mock('MyClass');

        $service = new Service();
        $service->injectDependency($mock);
    }
}

?>
-----
<?php

namespace Rector\MockeryToProphecy\Tests\Rector\ClassMethod\MockeryToProphecyRector\Fixture;

use PHPUnit\Framework\TestCase;

final class MockCreation extends TestCase
{
    public function testSomething()
    {
        $mock = $this->prophesize('MyClass');

        $service = new Service();
        $service->injectDependency($mock->reveal());
    }
}

?>
