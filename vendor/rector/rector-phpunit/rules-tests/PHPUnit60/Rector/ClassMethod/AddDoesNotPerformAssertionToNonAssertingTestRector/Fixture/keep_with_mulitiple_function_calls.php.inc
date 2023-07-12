<?php

namespace Rector\PHPUnit\Tests\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Fixture;

use PHPUnit\Framework\TestCase;

class KeepWithMultipleFunctionCalls extends TestCase
{
    /**
     * @dataProvider trueProvider
     */
    public function testTrue(bool $bool): void
    {
        $this->refreshFixtures();
        $this->doSomeOtherStuff();

        $this->handleAssert($bool);
    }

    /**
     * @return bool[]
     */
    public function trueProvider(): array
    {
        return [
            [true],
        ];
    }

    private function handleAssert(bool $bool): void
    {
        // some generic function that can be used for multiple test to do the same assertions.
        self::assertTrue($bool);
    }

    protected function refreshFixtures(): void
    {
        // TODO: Implement refreshFixtures() method.
    }

    protected function doSomeOtherStuff(): void
    {
        // TODO: Implement doSomeOtherStuff() method.
    }
}
