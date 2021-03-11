<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\MigrateAtToWithConsecutiveAndWillReturnOnConsecutiveCallsRector\Fixture;

class ToBeMocked
{
    public function create()
    {
    }
}

final class DifferentReturnValues extends \PHPUnit\Framework\TestCase
{
    public function test(): void
    {
        $mock = $this->createMock(ToBeMocked::class);
        $mock
            ->expects($this->at(0))
            ->method('create')
            ->willReturn('1');
        $reference = '2';
        $mock
            ->expects($this->at(1))
            ->method('create')
            ->willReturnReference($reference);
        $mock
            ->expects($this->at(2))
            ->method('create')
            ->willReturnMap(['foo' => 'bar']);
        $mock
            ->expects($this->at(3))
            ->method('create')
            ->willReturnArgument(1);
        $mock
            ->expects($this->at(4))
            ->method('create')
            ->willReturnCallback(static function () {
                return null;
            });
        $mock
            ->expects($this->at(5))
            ->method('create')
            ->willReturnSelf();
        $mock
            ->expects($this->at(6))
            ->method('create')
            ->willThrowException(new \Exception());
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

final class DifferentReturnValues extends \PHPUnit\Framework\TestCase
{
    public function test(): void
    {
        $mock = $this->createMock(ToBeMocked::class);
        $reference = '2';
        $mock->method('create')->willReturnOnConsecutiveCalls('1', new \PHPUnit\Framework\MockObject\Stub\ReturnReference($reference), $this->returnValueMap(['foo' => 'bar']), $this->returnArgument(1), $this->returnCallback(static function () {
            return null;
        }), $this->returnSelf(), $this->throwException(new \Exception()));
    }
}

?>
