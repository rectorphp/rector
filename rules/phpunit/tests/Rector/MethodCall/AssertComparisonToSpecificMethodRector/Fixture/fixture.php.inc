<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertComparisonToSpecificMethodRector\Fixture;

final class MyEqualsTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertFalse($expected === $anything);
        $this->assertTrue(count($something) > 2);
        $this->assertTrue($something[0]['foo'] > $something[1]['foo']);
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

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertComparisonToSpecificMethodRector\Fixture;

final class MyEqualsTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertNotSame($expected, $anything);
        $this->assertGreaterThan(2, count($something));
        $this->assertGreaterThan($something[1]['foo'], $something[0]['foo']);
        $this->assertNotEquals(__DIR__, $something, 'message');
        $this->assertSame(1.0, $something);
        $this->assertNotSame(true, in_array('foo', ['bar', 'baz'], true));
        $this->assertNotEquals('string', gettype($foo));
        $this->assertEquals(['foo', 'bar'], $something);
    }
}

?>
