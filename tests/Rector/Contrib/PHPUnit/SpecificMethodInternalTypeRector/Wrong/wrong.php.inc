<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertTrue(is_string($something), 'third argument');
        $this->assertFalse(is_string($something), 'third argument');
        $this->assertTrue(is_array($something), 'third argument');
        $this->assertFalse(is_array($something), 'third argument');
        $this->assertTrue(is_numeric($something), 'third argument');
        $this->assertFalse(is_numeric($something), 'third argument');
        $this->assertTrue(is_integer($something), 'third argument');
        $this->assertFalse(is_integer($something), 'third argument');
        $this->assertTrue(is_float($something), 'third argument');
        $this->assertFalse(is_float($something), 'third argument');
        $this->assertTrue(is_bool($something), 'third argument');
        $this->assertFalse(is_bool($something), 'third argument');
        $this->assertTrue(is_object($something), 'third argument');
        $this->assertFalse(is_object($something), 'third argument');
        $this->assertTrue(is_resource($something), 'third argument');
        $this->assertFalse(is_resource($something), 'third argument');
        $this->assertTrue(is_scalar($something), 'third argument');
        $this->assertFalse(is_scalar($something), 'third argument');
        $this->assertTrue(is_callable($something), 'third argument');
        $this->assertFalse(is_callable($something), 'third argument');
        $this->assertTrue(is_file($something), 'second argument');
        $this->assertFalse(is_file($something), 'second argument');
        $this->assertTrue(is_dir($something), 'second argument');
        $this->assertFalse(is_dir($something), 'second argument');
        $this->assertTrue(is_null($something), 'second argument');
        $this->assertFalse(is_null($something), 'second argument');
        $this->assertTrue(is_a($something), 'second argument');
        $this->assertFalse(is_a($something), 'second argument');
        $this->assertTrue(is_link($something), 'second argument');
        $this->assertFalse(is_link($something), 'second argument');
    }
}
