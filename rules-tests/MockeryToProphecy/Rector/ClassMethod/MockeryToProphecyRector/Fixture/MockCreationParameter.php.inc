<?php

namespace Rector\Tests\MockeryToProphecy\Rector\ClassMethod\MockeryToProphecyRector\Fixture;

use PHPUnit\Framework\TestCase;

final class MockCreationParameter extends TestCase
{
    public function testSomething()
    {
        $service = new Service();
        $service->injectDependency(\Mockery::mock('MyClass'));
    }
}

?>
-----
<?php

namespace Rector\Tests\MockeryToProphecy\Rector\ClassMethod\MockeryToProphecyRector\Fixture;

use PHPUnit\Framework\TestCase;

final class MockCreationParameter extends TestCase
{
    public function testSomething()
    {
        $service = new Service();
        $service->injectDependency($this->prophesize('MyClass')->reveal());
    }
}

?>
