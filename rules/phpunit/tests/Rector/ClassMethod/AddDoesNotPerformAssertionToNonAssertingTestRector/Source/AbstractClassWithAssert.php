<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Source;

use PHPUnit\Framework\TestCase;

abstract class AbstractClassWithAssert extends TestCase
{
    public function doAssertThis()
    {
        $this->anotherMethod();
    }

    private function anotherMethod()
    {
        $this->assertTrue(true);
    }
}
