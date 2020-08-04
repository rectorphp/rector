<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertCompareToSpecificMethodRector\Fixture;

final class MyAssertCompareTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertEquals('string', gettype($something));
        $this->assertEquals('string', $something['property']());
        $this->assertNotSame($foo[1]->result, count($this->results));
        $this->assertSame(1, $count);
        $this->assertNotSame(2, $foo->count());
        $this->assertEquals($count, $foo->sizeof);
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertCompareToSpecificMethodRector\Fixture;

final class MyAssertCompareTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertInternalType('string', $something);
        $this->assertEquals('string', $something['property']());
        $this->assertNotCount($foo[1]->result, $this->results);
        $this->assertSame(1, $count);
        $this->assertNotSame(2, $foo->count());
        $this->assertEquals($count, $foo->sizeof);
    }
}

?>
