<?php

namespace Rector\PHPUnit\Tests\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Fixture;

use PHPUnit\Framework\TestCase;

class Blah {}
class Other {}

class TestSomething extends TestCase
{
    public function test_blah()
    {
        $a = $this->createMock(Blah::class);

        $a->expects($this->once())->method('something');

        (new Other())->whatever($a);
    }
}
