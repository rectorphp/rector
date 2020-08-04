<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertNotOperatorRector\Fixture;

final class MyNotOperatorTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertFalse(! ($something === false));
        $this->assertTrue(! $something);
        self::assertTrue(! $something);
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertNotOperatorRector\Fixture;

final class MyNotOperatorTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertTrue($something === false);
        $this->assertFalse($something);
        self::assertFalse($something);
    }
}

?>
