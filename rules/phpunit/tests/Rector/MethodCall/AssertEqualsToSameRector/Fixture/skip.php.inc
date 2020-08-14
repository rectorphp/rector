<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertEqualsToSameRector\Fixture;

use PHPUnit\Framework\TestCase;

final class MySkipTest extends TestCase
{
    public function test()
    {
        $null = null;
        $expectedNull = null;
        $this->assertEquals($expectedNull, $null);

        $bool = true;
        $expectedBool = true;
        $this->assertEquals($expectedBool, $bool);

        $array = [];
        $expectedArray = [];
        $this->assertEquals($expectedArray, $array);
    }
}
