<?php

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Fixture;

class KeepNonPublic extends \PHPUnit\Framework\TestCase
{
    protected function testDelegated()
    {
    }
}
