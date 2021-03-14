<?php

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\MigrateAtToConsecutiveExpectationsRector\Fixture;

class Foo
{
    public function someMethod()
    {
    }
}
final class WillCallbacksAreKept extends \PHPUnit\Framework\TestCase
{
    public function test(): void
    {
        $mock = $this->createMock(Foo::class);
        $mock
            ->expects($this->at(0))
            ->method('someMethod')
            ->will($this->throwException(new \Exception()));
        $mock
            ->expects($this->at(1))
            ->method('someMethod')
            ->will($this->returnValue('1'));
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\MigrateAtToConsecutiveExpectationsRector\Fixture;

class Foo
{
    public function someMethod()
    {
    }
}
final class WillCallbacksAreKept extends \PHPUnit\Framework\TestCase
{
    public function test(): void
    {
        $mock = $this->createMock(Foo::class);
        $mock->method('someMethod')->willReturnOnConsecutiveCalls($this->throwException(new \Exception()), $this->returnValue('1'));
    }
}

?>
