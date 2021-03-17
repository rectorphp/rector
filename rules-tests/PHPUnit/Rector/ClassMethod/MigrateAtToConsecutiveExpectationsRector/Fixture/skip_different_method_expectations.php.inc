<?php


namespace Rector\PHPUnit\Tests\Rector\ClassMethod\MigrateAtToConsecutiveExpectationsRector\Fixture;

class Foo
{
    public function someMethod()
    {
    }
    public function someOtherMethod()
    {
    }
}
final class SkipDifferentMethodExpectations extends \PHPUnit\Framework\TestCase
{
    public function test(): void
    {
        $mock = $this->createMock(Foo::class);
        $mock
            ->expects($this->at(0))
            ->method('someMethod')
            ->willReturn('1');

        $mock
            ->expects($this->at(1))
            ->with('1')
            ->method('someOtherMethod');

        $mock
            ->expects($this->at(2))
            ->with('2')
            ->method('someMethod')
            ->willReturn('3');
    }
}

?>

