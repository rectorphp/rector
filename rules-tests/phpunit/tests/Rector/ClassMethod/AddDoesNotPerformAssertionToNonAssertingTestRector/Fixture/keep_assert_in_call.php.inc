<?php

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Fixture;

class KeepAssertInCall extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->doElsewhere(5);
    }

    private function doElsewhere($value)
    {
        $this->assertNotNull($value);
    }
}
