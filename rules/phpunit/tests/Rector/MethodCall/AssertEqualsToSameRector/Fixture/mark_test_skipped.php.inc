<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertPropertyExistsRector\Fixture;

final class TestSkipped extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->markTestSkipped('skipped');
        $this->assertEquals('foo', 'foo');
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertPropertyExistsRector\Fixture;

final class TestSkipped extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->markTestSkipped('skipped');
        $this->assertSame('foo', 'foo');
    }
}

?>
