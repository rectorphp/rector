<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Source;

use PHPUnit\Framework\TestCase;

abstract class AbstractClassWithStaticAssert extends TestCase
{
    public function doAssertThis()
    {
        self::anotherMethod();
    }

    private static function anotherMethod()
    {
        self::assertTrue(true);
    }
}
