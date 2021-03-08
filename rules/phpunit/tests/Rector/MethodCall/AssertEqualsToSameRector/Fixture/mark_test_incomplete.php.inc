<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertPropertyExistsRector\Fixture;

final class TestIncomplete extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->markTestIncomplete('incomplete');
        $this->assertEquals('foo', 'foo');
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertPropertyExistsRector\Fixture;

final class TestIncomplete extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->markTestIncomplete('incomplete');
        $this->assertSame('foo', 'foo');
    }
}

?>
