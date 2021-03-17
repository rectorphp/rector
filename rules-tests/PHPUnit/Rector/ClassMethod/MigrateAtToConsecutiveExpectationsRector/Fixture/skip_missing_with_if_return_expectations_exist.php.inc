<?php

namespace Rector\Tests\PHPUnit\Rector\ClassMethod\MigrateAtToConsecutiveExpectationsRector\Fixture;

class Foo
{
    public function someMethod()
    {
    }
}
final class SkipMissingWithIfReturnExpectationsExist extends \PHPUnit\Framework\TestCase
{
    public function test(): void
    {
        $mock = $this->createMock(Foo::class);
        $mock
            ->expects($this->at(0))
            ->with('0')
            ->method('someMethod')
            ->willReturn('1');

        $mock
            ->expects($this->at(2))
            ->with('2')
            ->method('someMethod')
            ->willReturn('3');
    }
}

?>
