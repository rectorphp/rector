<?php

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Fixture;

use InvalidArgumentException;

class KeepExpectedException extends \PHPUnit\Framework\TestCase
{
    /**
     * @expectedException \Exception
     */
    public function test()
    {
    }

    public function testExpectException()
    {
        $this->expectException('Throwable');
        throw new InvalidArgumentException();
    }

    public function testSetExpectedException()
    {
        $this->setExpectedException('Throwable');
    }
}
