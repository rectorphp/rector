<?php declare(strict_types=1);

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
