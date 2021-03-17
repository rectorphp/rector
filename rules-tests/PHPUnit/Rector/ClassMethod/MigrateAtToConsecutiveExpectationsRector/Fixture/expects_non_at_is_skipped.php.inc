<?php

namespace Rector\Tests\PHPUnit\Rector\ClassMethod\MigrateAtToConsecutiveExpectationsRector\Fixture;

class Foo
{
    public function someMethod()
    {
    }
}
final class ExpectsNonAtIsSkipped extends \PHPUnit\Framework\TestCase
{
    public function test(): void
    {
        $mock = $this->createMock(Foo::class);
        $mock
            ->expects($this->exactly(1))
            ->method('someMethod');
    }
}

?>
