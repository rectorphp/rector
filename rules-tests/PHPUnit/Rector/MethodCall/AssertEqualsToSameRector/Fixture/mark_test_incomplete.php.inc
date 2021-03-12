<?php

namespace Rector\Tests\PHPUnit\Rector\MethodCall\AssertEqualsToSameRector\Fixture;

use PHPUnit\Framework\TestCase;

final class TestIncomplete extends TestCase
{
    public function test()
    {
        $this->markTestIncomplete('incomplete');

        $this->assertEquals('foo', 'foo');
        $this->assertEquals(123, 123);
        $this->assertEquals(1.23, 1.23);
    }
}

?>
-----
<?php

namespace Rector\Tests\PHPUnit\Rector\MethodCall\AssertEqualsToSameRector\Fixture;

use PHPUnit\Framework\TestCase;

final class TestIncomplete extends TestCase
{
    public function test()
    {
        $this->markTestIncomplete('incomplete');

        $this->assertSame('foo', 'foo');
        $this->assertSame(123, 123);
        $this->assertSame(1.23, 1.23);
    }
}

?>
