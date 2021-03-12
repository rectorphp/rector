<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertTrueFalseToSpecificMethodRector\Fixture;

final class SkipMethodCall extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $someObject = new Foo();
        self::assertFalse($someObject->someMethod());
    }
}
