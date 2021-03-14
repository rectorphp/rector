<?php

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\MigrateAtToConsecutiveExpectationsRector\Fixture;

class Foo
{
    public function someMethod()
    {
    }
}
final class WillReturnOnly extends \PHPUnit\Framework\TestCase
{
    public function test(): void
    {
        $mock = $this->createMock(Foo::class);
        $mock
            ->expects($this->at(0))
            ->method('someMethod')
            ->willReturn('0');
        $mock
            ->expects($this->at(1))
            ->method('someMethod')
            ->willReturn('1');
        $mock
            ->expects($this->at(2))
            ->method('someMethod')
            ->willReturn('2');
        $mock
            ->expects($this->at(3))
            ->method('someMethod')
            ->willReturn(null);
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
final class WillReturnOnly extends \PHPUnit\Framework\TestCase
{
    public function test(): void
    {
        $mock = $this->createMock(Foo::class);
        $mock->method('someMethod')->willReturnOnConsecutiveCalls('0', '1', '2', null);
    }
}

?>
