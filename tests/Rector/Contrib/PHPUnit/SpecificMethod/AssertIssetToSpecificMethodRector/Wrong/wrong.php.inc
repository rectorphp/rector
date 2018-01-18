<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertTrue(isset($node->value1));
        $this->assertFalse(isset($node['value2']));
    }
}
