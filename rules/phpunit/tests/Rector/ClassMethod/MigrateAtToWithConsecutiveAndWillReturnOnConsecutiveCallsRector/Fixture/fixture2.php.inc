<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\MigrateAtToWithConsecutiveAndWillReturnOnConsecutiveCallsRector\Fixture;

use Exception;

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
            ->expects($this->exactly(3))
            ->method('create');

        $mock
            ->expects($this->at(0))
            ->with('0')
            ->method('create')
            ->willReturn('1');

        $mock
            ->expects($this->at(1)) // the middle creation will error
            ->with('1')
            ->method('create')
            ->willThrowException(new Exception());

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

use Exception;

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
            ->expects($this->exactly(3))
            ->method('create')
            ->withConsecutive(['0'], ['1'], ['2'])
            ->method('create')
            ->willReturnOnConsecutiveCalls(
                '1',
                $this->throwException(new RuntimeException('Some message')),
                '3'
            );
    }
}

?>
