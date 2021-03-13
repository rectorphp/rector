<?php

namespace Rector\Tests\PHPUnit\Rector\MethodCall\AssertFalseStrposToContainsRector\Fixture;

final class SkipStaticCall extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        self::assertFalse(SomeClass::someMethod());
    }
}
