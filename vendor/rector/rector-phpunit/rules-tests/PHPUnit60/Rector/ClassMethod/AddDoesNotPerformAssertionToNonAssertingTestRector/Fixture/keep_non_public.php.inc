<?php

namespace Rector\PHPUnit\Tests\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Fixture;

class KeepNonPublic extends \PHPUnit\Framework\TestCase
{
    protected function testDelegated()
    {
    }
}
