<?php

namespace Rector\PHPUnit\Tests\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Fixture;

class SkipExpectOutputTest extends \PHPUnit\Framework\TestCase
{
    public function testExpectOutputString()
    {
        $this->expectOutputString('Hello world!');

        print 'Hello world!';
    }

    public function testExpectOutputRegex()
    {
        $this->expectOutputRegex('#^Hello world!$#');

        print 'Hello world!';
    }
}
