<?php

namespace Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertCompareToSpecificMethodRector\Wrong;

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertSame(5, count($something));
        $this->assertEquals(10, iterator_count($something));
        $this->assertNotEquals($count, sizeof($something), 'third argument');
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

namespace Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertCompareToSpecificMethodRector\Wrong;

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertCount(5, $something);
        $this->assertCount(10, $something);
        $this->assertNotCount($count, $something, 'third argument');
        $this->assertInternalType('string', $something);
        $this->assertEquals('string', $something['property']());
        $this->assertNotCount($foo[1]->result, $this->results);
        $this->assertSame(1, $count);
        $this->assertNotSame(2, $foo->count());
        $this->assertEquals($count, $foo->sizeof);
    }
}

?>
