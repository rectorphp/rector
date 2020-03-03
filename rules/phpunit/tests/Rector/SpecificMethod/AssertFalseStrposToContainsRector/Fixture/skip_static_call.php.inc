<?php

namespace Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertFalseStrposToContainsRector\Fixture;

final class SkipStaticCall extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        self::assertFalse(SomeClass::someMethod());
    }
}
