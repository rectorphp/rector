<?php

namespace Rector\Tests\PHPUnit\Rector\MethodCall\AssertEqualsToSameRector\Fixture;

use PHPUnit\Framework\TestCase;

final class MyTest extends TestCase
{
    public function test()
    {
        $int = 1;
        $expectedInt = 1;
        $this->assertEquals($expectedInt, $int);

        $float = 1.1;
        $expectedFloat = 1.1;
        $this->assertEquals($expectedFloat, $float);

        $string = 'abc';
        $expectedString = 'abc';
        $this->assertEquals($expectedString, $string);
    }
}

?>
-----
<?php

namespace Rector\Tests\PHPUnit\Rector\MethodCall\AssertEqualsToSameRector\Fixture;

use PHPUnit\Framework\TestCase;

final class MyTest extends TestCase
{
    public function test()
    {
        $int = 1;
        $expectedInt = 1;
        $this->assertSame($expectedInt, $int);

        $float = 1.1;
        $expectedFloat = 1.1;
        $this->assertSame($expectedFloat, $float);

        $string = 'abc';
        $expectedString = 'abc';
        $this->assertSame($expectedString, $string);
    }
}

?>
