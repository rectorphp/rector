<?php

namespace Rector\SOLID\Tests\Rector\ClassMethod\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

use PHPUnit\Framework\TestCase;

class SkipTestVariableTest extends TestCase
{
    public function test()
    {
        $expectedValue = 1;
        $result = random(1, 5);

        $this->assertSame($expectedValue, $result);
    }
}
