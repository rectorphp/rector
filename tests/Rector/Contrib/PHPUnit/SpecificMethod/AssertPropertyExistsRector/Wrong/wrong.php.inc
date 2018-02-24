<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertTrue(property_exists(new stdClass(), 'property'));
        $this->assertFalse(property_exists(new \Namespaced\stdClass, 'property'), 'message');
    }
}
