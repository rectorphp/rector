<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertTrueFalseInternalTypeToSpecificMethodRector\Fixture;

final class Fixture2Test extends \PHPUnit\Framework\TestCase
{
    public function testSomething(object $object): void
    {
        self::assertFalse($object->someMethod());
    }
}
