<?php

namespace Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertFalseStrposToContainsRector\Wrong;

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertNotFalse(strpos($node, 'foo'));
        $this->assertFalse(stripos($node, 'foo'), 'message');
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertFalseStrposToContainsRector\Wrong;

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertContains('foo', $node);
        $this->assertNotContains('foo', $node, 'message');
    }
}

?>
