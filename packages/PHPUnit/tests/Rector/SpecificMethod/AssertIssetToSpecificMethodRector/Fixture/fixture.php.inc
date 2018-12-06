<?php

namespace Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertIssetToSpecificMethodRector\Wrong;

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertTrue(isset($node->value1));
        $this->assertFalse(isset($node->value2), 'message');
        $this->assertTrue(isset($node['value1']), 'message');
        $this->assertFalse(isset($node['value2']));
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertIssetToSpecificMethodRector\Wrong;

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertObjectHasAttribute('value1', $node);
        $this->assertObjectNotHasAttribute('value2', $node, 'message');
        $this->assertArrayHasKey('value1', $node, 'message');
        $this->assertArrayNotHasKey('value2', $node);
    }
}

?>
