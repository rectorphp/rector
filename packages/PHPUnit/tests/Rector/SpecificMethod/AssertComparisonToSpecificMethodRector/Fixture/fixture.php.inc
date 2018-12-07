<?php

namespace Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertComparisonToSpecificMethodRector\Fixture;

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertFalse($expected === $anything);
        $this->assertTrue(count($something) > 2);
        $this->assertTrue(__DIR__ <> $something, 'message');
        $this->assertTrue(1.0 === $something);
        $this->assertFalse(true === in_array('foo', ['bar', 'baz'], true));
        $this->assertTrue('string' != gettype($foo));
        $this->assertTrue(['foo', 'bar'] == $something);
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertComparisonToSpecificMethodRector\Fixture;

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertNotSame($expected, $anything);
        $this->assertGreaterThan(2, count($something));
        $this->assertNotEquals(__DIR__, $something, 'message');
        $this->assertSame(1.0, $something);
        $this->assertNotSame(true, in_array('foo', ['bar', 'baz'], true));
        $this->assertNotEquals('string', gettype($foo));
        $this->assertEquals(['foo', 'bar'], $something);
    }
}

?>
