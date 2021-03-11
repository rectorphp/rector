<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\MigrateAtToWithConsecutiveAndWillReturnOnConsecutiveCallsRector\Fixture;

class ToBeMocked
{
    public function create()
    {
    }
}

final class WithMultipleArguments extends \PHPUnit\Framework\TestCase
{
    public function test(): void
    {
        $mock = $this->createMock(ToBeMocked::class);
        $mock
            ->expects($this->at(0))
            ->with('0', '1')
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

final class WithMultipleArguments extends \PHPUnit\Framework\TestCase
{
    public function test(): void
    {
        $mock = $this->createMock(ToBeMocked::class);
        $mock->method('create')->withConsecutive(['0', '1']);
    }
}

?>
