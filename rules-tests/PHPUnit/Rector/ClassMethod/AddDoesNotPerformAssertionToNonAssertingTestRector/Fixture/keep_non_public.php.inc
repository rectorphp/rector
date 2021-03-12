<?php

namespace Rector\Tests\PHPUnit\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Fixture;

class KeepNonPublic extends \PHPUnit\Framework\TestCase
{
    protected function testDelegated()
    {
    }
}
