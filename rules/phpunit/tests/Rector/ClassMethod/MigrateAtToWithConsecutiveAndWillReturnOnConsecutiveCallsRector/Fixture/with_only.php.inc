<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\MigrateAtToWithConsecutiveAndWillReturnOnConsecutiveCallsRector\Fixture;

class ToBeMocked
{
    public function create()
    {
    }
}

final class WithOnly extends \PHPUnit\Framework\TestCase
{
    public function test(): void
    {
        $mock = $this->createMock(ToBeMocked::class);
        $mock
            ->expects($this->at(0))
            ->with('0')
            ->method('create');
        $mock
            ->expects($this->at(1))
            ->with('1')
            ->method('create');
        $mock
            ->expects($this->at(2))
            ->with('2')
            ->method('create');
        $mock
            ->expects($this->at(3))
            ->with(null)
            ->method('create');
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

final class WithOnly extends \PHPUnit\Framework\TestCase
{
    public function test(): void
    {
        $mock = $this->createMock(ToBeMocked::class);
        $mock->method('create')->withConsecutive(['0'], ['1'], ['2'], [null]);
    }
}

?>
