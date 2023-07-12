<?php

namespace Rector\Tests\PHPUnit\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Fixture;

use Rector\PHPUnit\Tests\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Source\Factory;

final class SkipCallExpectNotToPerformAssertionMethodCall extends \PHPUnit\Framework\TestCase
{
    public function testCreate(): void
    {
        $this->expectNotToPerformAssertions();

        $factory = new Factory();

        $factory->create();
    }
}
