<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\MigrateAtToWithConsecutiveAndWillReturnOnConsecutiveCallsRector\Fixture;

class ToBeMocked
{
    public function create()
    {
    }
}

final class WillReturnOnly extends \PHPUnit\Framework\TestCase
{
    public function test(): void
    {
        $mock = $this->createMock(ToBeMocked::class);
        $mock
            ->expects($this->at(0))
            ->method('create')
            ->willReturn('0');
        $mock
            ->expects($this->at(1))
            ->method('create')
            ->willReturn('1');
        $mock
            ->expects($this->at(2))
            ->method('create')
            ->willReturn('2');
        $mock
            ->expects($this->at(3))
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

final class WillReturnOnly extends \PHPUnit\Framework\TestCase
{
    public function test(): void
    {
        $mock = $this->createMock(ToBeMocked::class);
        $mock->method('create')->willReturnOnConsecutiveCalls('0', '1', '2', null);
    }
}

?>
