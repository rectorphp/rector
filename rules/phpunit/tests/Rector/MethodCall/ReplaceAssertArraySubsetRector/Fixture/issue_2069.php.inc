<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\ReplaceAssertArraySubsetRector\Fixture;

class Issue2069 extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $result = ['Hello' => 'a', 'World!' => 'b'];
        $this->assertArraySubset(['World!' => 'b', 'Hello' => 'a'], $result);
    }

    public function shouldWorkToo()
    {
        $result = ['a', 'b', 'c', 'd'];
        $this->assertArraySubset(['b', 'c'], $result);
        $this->assertArraySubset(['a', 'c', 'b'], $result);
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\ReplaceAssertArraySubsetRector\Fixture;

class Issue2069 extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $result = ['Hello' => 'a', 'World!' => 'b'];
        $this->assertArrayHasKey('World!', $result);
        $this->assertArrayHasKey('Hello', $result);
        $this->assertEquals('b', $result['World!']);
        $this->assertEquals('a', $result['Hello']);
    }

    public function shouldWorkToo()
    {
        $result = ['a', 'b', 'c', 'd'];
        $this->assertTrue(array_intersect(['b', 'c'], $result) === ['b', 'c']);
        $this->assertTrue(array_intersect(['a', 'c', 'b'], $result) === ['a', 'c', 'b']);
    }
}

?>
