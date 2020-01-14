<?php

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Fixture;

class SkipExistingAnnotation extends \PHPUnit\Framework\TestCase
{
    /**
     * @doesNotPerformAssertion
     */
    public function testSomething(): void
    {
    }
}
