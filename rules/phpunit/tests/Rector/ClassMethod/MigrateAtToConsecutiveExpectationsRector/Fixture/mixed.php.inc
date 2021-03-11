<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\MigrateAtToWithConsecutiveAndWillReturnOnConsecutiveCallsRector\Fixture;

class ToBeMocked
{
    public function create()
    {
    }
}

final class Mixed extends \PHPUnit\Framework\TestCase
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
            ->method('create');

        $mock
            ->expects($this->at(2))
            ->with('2')
            ->method('create')
            ->willReturn('3');
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

final class Mixed extends \PHPUnit\Framework\TestCase
{
    public function test(): void
    {
        $mock = $this->createMock(ToBeMocked::class);

        $mock->method('create')->withConsecutive([null], ['1'], ['2'])->willReturnOnConsecutiveCalls('1', null, '3');
    }
}

?>
