<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\MigrateAtToWithConsecutiveAndWillReturnOnConsecutiveCallsRector\Fixture;

class ToBeMocked
{
    public function create()
    {
    }
}

final class KeepWillCallback extends \PHPUnit\Framework\TestCase
{
    public function test(): void
    {
        $mock = $this->createMock(ToBeMocked::class);
        $mock
            ->expects($this->at(0))
            ->method('create')
            ->will($this->throwException(new \Exception()));
        $mock
            ->expects($this->at(1))
            ->method('create')
            ->will($this->returnValue('1'));
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

final class KeepWillCallback extends \PHPUnit\Framework\TestCase
{
    public function test(): void
    {
        $mock = $this->createMock(ToBeMocked::class);
        $mock->method('create')->willReturnOnConsecutiveCalls($this->throwException(new \Exception()), $this->returnValue('1'));
    }
}

?>
