<?php

declare(strict_types=1);

namespace Rector\Tests\PHPUnit\Rector\MethodCall\AssertTrueFalseInternalTypeToSpecificMethodRector\Fixture;

use PHPUnit\Framework\TestCase;

final class SkipAssertFalseTest extends TestCase
{
    public function testSomething(object $object): void
    {
        self::assertFalse($object->someMethod());
    }
}
