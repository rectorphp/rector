<?php

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\MigrateAtToConsecutiveExpectationsRector\Fixture;

class Foo
{
    public function someMethod()
    {
    }
}
final class HandleMultipleVariables extends \PHPUnit\Framework\TestCase
{
    public function test(): void
    {
        $mock = $this->createMock(Foo::class);
        $mock
            ->expects($this->at(0))
            ->with('0')
            ->method('someMethod')
            ->willReturn('1');
        $mock
            ->expects($this->at(1))
            ->with('1')
            ->method('someMethod')
            ->willReturn('2');

        $mock2 = $this->createMock(Foo::class);
        $mock2
            ->expects($this->at(0))
            ->with('0')
            ->method('someMethod')
            ->willReturn('1');
        $mock2
            ->expects($this->at(1))
            ->with('1')
            ->method('someMethod')
            ->willReturn('2');
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
final class HandleMultipleVariables extends \PHPUnit\Framework\TestCase
{
    public function test(): void
    {
        $mock = $this->createMock(Foo::class);
        $mock->method('someMethod')->withConsecutive(['0'], ['1'])->willReturnOnConsecutiveCalls('1', '2');

        $mock2 = $this->createMock(Foo::class);
        $mock2->method('someMethod')->withConsecutive(['0'], ['1'])->willReturnOnConsecutiveCalls('1', '2');
    }
}

?>
