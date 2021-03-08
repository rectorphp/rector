<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertEqualsToSameRector\Fixture;

use PHPUnit\Framework\TestCase;

final class TestSkipped extends BTestCase
{
    public function test()
    {
        $this->markTestSkipped('skipped');
        $this->assertEquals('foo', 'foo');
        $this->assertEquals(123, 123);
        $this->assertEquals(1.23, 1.23);
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertEqualsToSameRector\Fixture;

use PHPUnit\Framework\TestCase;

final class TestSkipped extends TestCase
{
    public function test()
    {
        $this->markTestSkipped('skipped');
        $this->assertSame('foo', 'foo');
        $this->assertSame(123, 123);
        $this->assertSame(1.23, 1.23);
    }
}

?>
