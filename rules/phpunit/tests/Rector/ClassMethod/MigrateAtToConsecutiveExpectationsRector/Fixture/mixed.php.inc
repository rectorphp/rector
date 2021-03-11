<?php

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\MigrateAtToConsecutiveExpectationsRector\Fixture

class Foo
{
    public function someMethod()
    {
    }
}
final class Mixed extends \PHPUnit\Framework\TestCase
{
    public function test(): void
    {
        $mock = $this->createMock(Foo::class);
        $mock
            ->expects($this->at(0))
            ->method('someMethod')
            ->willReturn('1');

        $mock
            ->expects($this->at(1))
            ->with('1')
            ->method('someMethod');

        $mock
            ->expects($this->at(2))
            ->with('2')
            ->method('someMethod')
            ->willReturn('3');
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\MigrateAtToConsecutiveExpectationsRector\Fixture

class Foo
{
    public function someMethod()
    {
    }
}
final class Mixed extends \PHPUnit\Framework\TestCase
{
    public function test(): void
    {
        $mock = $this->createMock(Foo::class);

        $mock->method('someMethod')->withConsecutive([null], ['1'], ['2'])->willReturnOnConsecutiveCalls('1', null, '3');
    }
}

?>
