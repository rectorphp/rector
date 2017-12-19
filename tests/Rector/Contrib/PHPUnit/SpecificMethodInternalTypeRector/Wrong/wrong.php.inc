<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertTrue(is_string($something));
        $this->assertFalse(is_string($something));
        $this->assertTrue(is_array($something));
        $this->assertFalse(is_array($something));
        $this->assertTrue(is_numeric($something));
        $this->assertFalse(is_numeric($something));
        $this->assertTrue(is_integer($something));
        $this->assertFalse(is_integer($something));
        $this->assertTrue(is_float($something));
        $this->assertFalse(is_float($something));
        $this->assertTrue(is_bool($something));
        $this->assertFalse(is_bool($something));
        $this->assertTrue(is_object($something));
        $this->assertFalse(is_object($something));
        $this->assertTrue(is_resource($something));
        $this->assertFalse(is_resource($something));
        $this->assertTrue(is_scalar($something));
        $this->assertFalse(is_scalar($something));
        $this->assertTrue(is_callable($something));
        $this->assertFalse(is_callable($something));
        $this->assertTrue(is_file($something));
        $this->assertFalse(is_file($something));
        $this->assertTrue(is_dir($something));
        $this->assertFalse(is_dir($something));
        $this->assertTrue(is_null($something));
        $this->assertFalse(is_null($something));
        $this->assertTrue(is_a($something));
        $this->assertFalse(is_a($something));
        $this->assertTrue(is_link($something));
        $this->assertFalse(is_link($something));
    }
}
