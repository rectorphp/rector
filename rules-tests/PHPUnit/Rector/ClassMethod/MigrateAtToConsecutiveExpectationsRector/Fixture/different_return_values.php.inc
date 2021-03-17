<?php

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\MigrateAtToConsecutiveExpectationsRector\Fixture;

class Foo
{
    public function someMethod()
    {
    }
}
final class DifferentReturnValues extends \PHPUnit\Framework\TestCase
{
    public function test(): void
    {
        $mock = $this->createMock(Foo::class);
        $mock
            ->expects($this->at(0))
            ->method('someMethod')
            ->willReturn('1');
        $reference = '2';
        $mock
            ->expects($this->at(1))
            ->method('someMethod')
            ->willReturnReference($reference);
        $mock
            ->expects($this->at(2))
            ->method('someMethod')
            ->willReturnMap(['foo' => 'bar']);
        $mock
            ->expects($this->at(3))
            ->method('someMethod')
            ->willReturnArgument(1);
        $mock
            ->expects($this->at(4))
            ->method('someMethod')
            ->willReturnCallback(static function () {
                return null;
            });
        $mock
            ->expects($this->at(5))
            ->method('someMethod')
            ->willReturnSelf();
        $mock
            ->expects($this->at(6))
            ->method('someMethod')
            ->willThrowException(new \Exception());
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
final class DifferentReturnValues extends \PHPUnit\Framework\TestCase
{
    public function test(): void
    {
        $mock = $this->createMock(Foo::class);
        $reference = '2';
        $mock->method('someMethod')->willReturnOnConsecutiveCalls('1', new \PHPUnit\Framework\MockObject\Stub\ReturnReference($reference), $this->returnValueMap(['foo' => 'bar']), $this->returnArgument(1), $this->returnCallback(static function () {
            return null;
        }), $this->returnSelf(), $this->throwException(new \Exception()));
    }
}

?>
