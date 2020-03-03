<?php

namespace Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertTrueFalseToSpecificMethodRector\Fixture;

final class Fixture3Test extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        self::assertFalse(SomeClass::someMethod());
    }
}
