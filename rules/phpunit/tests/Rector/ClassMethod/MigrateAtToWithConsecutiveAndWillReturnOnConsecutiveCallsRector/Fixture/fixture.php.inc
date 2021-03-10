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
            ->expects($this->exactly(3))
            ->method('create')
            ->withConsecutive(['1'], ['2'], ['3']);

        $mock
            ->expects($this->at(1)) // the middle creation will error
            ->method('create')
            ->willThrowException(new Exception());
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
        $mock
            ->expects($this->exactly(3))
            ->method('create')
            ->withConsecutive(['1'], ['2'], ['3'])
            ->willReturnOnConsecutiveCalls(
                null,
                $this->throwException(new RuntimeException('Some message')),
                null
            );
    }
}

?>
