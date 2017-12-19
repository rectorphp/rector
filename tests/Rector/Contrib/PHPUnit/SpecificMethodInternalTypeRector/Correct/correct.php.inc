<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertInternalType('string', $something, 'third argument');
        $this->assertNotInternalType('string', $something, 'third argument');
        $this->assertInternalType('array', $something, 'third argument');
        $this->assertNotInternalType('array', $something, 'third argument');
        $this->assertInternalType('numeric', $something, 'third argument');
        $this->assertNotInternalType('numeric', $something, 'third argument');
        $this->assertInternalType('integer', $something, 'third argument');
        $this->assertNotInternalType('integer', $something, 'third argument');
        $this->assertInternalType('float', $something, 'third argument');
        $this->assertNotInternalType('float', $something, 'third argument');
        $this->assertInternalType('bool', $something, 'third argument');
        $this->assertNotInternalType('bool', $something, 'third argument');
        $this->assertInternalType('object', $something, 'third argument');
        $this->assertNotInternalType('object', $something, 'third argument');
        $this->assertInternalType('resource', $something, 'third argument');
        $this->assertNotInternalType('resource', $something, 'third argument');
        $this->assertInternalType('scalar', $something, 'third argument');
        $this->assertNotInternalType('scalar', $something, 'third argument');
        $this->assertInternalType('callable', $something, 'third argument');
        $this->assertNotInternalType('callable', $something, 'third argument');
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
