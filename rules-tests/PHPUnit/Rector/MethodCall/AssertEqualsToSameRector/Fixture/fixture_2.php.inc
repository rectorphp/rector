<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertEqualsToSameRector\Fixture;

use PHPUnit\Framework\TestCase;

final class MyTest2 extends TestCase
{
    public function test()
    {
        $int = 1;
        $expectedInt = 1;
        self::assertEquals($expectedInt, $int);

        $float = 1.1;
        $expectedFloat = 1.1;
        self::assertEquals($expectedFloat, $float);

        $string = 'abc';
        $expectedString = 'abc';
        self::assertEquals($expectedString, $string);
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertEqualsToSameRector\Fixture;

use PHPUnit\Framework\TestCase;

final class MyTest2 extends TestCase
{
    public function test()
    {
        $int = 1;
        $expectedInt = 1;
        self::assertSame($expectedInt, $int);

        $float = 1.1;
        $expectedFloat = 1.1;
        self::assertSame($expectedFloat, $float);

        $string = 'abc';
        $expectedString = 'abc';
        self::assertSame($expectedString, $string);
    }
}

?>
