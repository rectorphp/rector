<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\MigrateAtToWithConsecutiveAndWillReturnOnConsecutiveCallsRector\Fixture;

class ToBeMocked
{
    public function create()
    {
    }
}

final class Foo extends \PHPUnit\Framework\TestCase
{
    public function test(): void
    {
        $mock = $this->createMock(ToBeMocked::class);
        $mock
            ->expects($this->at(0))
            ->method('create')
            ->willReturn('1');
        $mock
            ->expects($this->at(1))
            ->with('1')
            ->method('create')
            ->willReturn('2');
        $returnValue = true;
        $mock
            ->expects($this->at(3))
            ->with('2')
            ->method('create')
            ->willReturn($returnValue);
        $mock
            ->expects($this->at(4))
            ->with('2')
            ->method('create');
        $mock
            ->expects($this->at(2))
            ->method('create')
            ->willReturn(null);
        $mock
            ->expects($this->at(5))
            ->with('1', 2, true, ['foo' => 'bar'])
            ->method('create')
            ->willReturn(null);
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\MigrateAtToWithConsecutiveAndWillReturnOnConsecutiveCallsRector\Fixture;

class ToBeMocked
{
    public function create()
    {
    }
}

final class Foo extends \PHPUnit\Framework\TestCase
{
    public function test(): void
    {
        $mock = $this->createMock(ToBeMocked::class);
        $returnValue = true;
        $mock->method('create')->withConsecutive([null], ['1'], [null], ['2'], ['2'], ['1', 2, true, ['foo' => 'bar']])->willReturnOnConsecutiveCalls('1', '2', null, $returnValue, null, null);
    }
}

?>
